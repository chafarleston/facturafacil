<?php

namespace Tests\MySQL\Restaurant;

use App\Http\Middleware\SystemLock;
use App\Models\Company;
use App\Models\Floor;
use App\Models\Product;
use App\Models\RestaurantKitchenBatch;
use App\Models\RestaurantOrder;
use App\Models\RestaurantOrderItem;
use App\Models\RestaurantTable;
use App\Models\Setting;
use App\Models\User;
use App\Services\KitchenBatchService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class KitchenBatchStatusTest extends TestCase
{
    use DatabaseTransactions;

    private Company $company;
    private RestaurantTable $table;
    private Product $product;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(SystemLock::class);

        $this->company = Company::create([
            'ruc' => '20100000001',
            'razon_social' => 'Empresa de prueba',
            'estado' => 'ACTIVO',
        ]);
        $this->company->is_main = true;
        $this->company->save();
        Company::clearCache();

        $floor = Floor::create([
            'company_id' => $this->company->id,
            'name' => 'Salón de prueba',
            'status' => 'ACTIVE',
        ]);

        $this->table = RestaurantTable::create([
            'company_id' => $this->company->id,
            'floor_id' => $floor->id,
            'name' => 'Mesa de prueba',
            'status' => 'OCCUPIED',
        ]);

        $this->product = Product::create([
            'company_id' => $this->company->id,
            'codigo' => 'TEST-001',
            'descripcion' => 'Producto de prueba',
            'precio' => 10,
            'estado' => 'ACTIVO',
        ]);

        $this->user = User::factory()->create([
            'company_id' => $this->company->id,
            'role' => 'admin',
        ]);
    }

    public function test_todos_los_items_cancelados_no_marcan_el_pedido_como_entregado(): void
    {
        $order = $this->createOrder('SENT_TO_KITCHEN');
        $this->createItem($order, 'CANCELLED');
        $this->createBatch($order, 'CANCELLED');

        app(KitchenBatchService::class)->syncOrderStatus($order);

        $this->assertSame('SENT_TO_KITCHEN', $order->fresh()->status);
    }

    public function test_items_entregados_marcan_el_pedido_como_entregado(): void
    {
        $order = $this->createOrder('SENT_TO_KITCHEN');
        $this->createItem($order, 'DELIVERED');
        $this->createBatch($order, 'DELIVERED');

        app(KitchenBatchService::class)->syncOrderStatus($order);

        $this->assertSame('DELIVERED', $order->fresh()->status);
    }

    public function test_prepare_y_deliver_actualizan_el_pedido_y_los_items(): void
    {
        $order = $this->createOrder('SENT_TO_KITCHEN');
        $item = $this->createItem($order, 'SENT');
        $batch = $this->createBatch($order, 'PENDING');
        $item->update(['kitchen_batch_id' => $batch->id]);

        $service = app(KitchenBatchService::class);
        $service->prepare($batch->fresh());

        $this->assertSame('PREPARING', $order->fresh()->status);
        $this->assertSame('PREPARING', $batch->fresh()->status);

        $service->deliver($batch->fresh());

        $this->assertSame('DELIVERED', $order->fresh()->status);
        $this->assertSame('DELIVERED', $item->fresh()->kitchen_status);
    }

    public function test_crear_lote_dos_veces_no_duplica_lotes(): void
    {
        $order = $this->createOrder('OPEN');
        $item = $this->createItem($order, 'PENDING');

        $service = app(KitchenBatchService::class);

        $first = $service->createFromItems($order, collect([$item]), $this->user->id);
        $second = $service->createFromItems(
            $order->fresh(),
            collect([$item->fresh()]),
            $this->user->id
        );

        $this->assertCount(1, $first);
        $this->assertCount(0, $second);
        $this->assertSame(1, RestaurantKitchenBatch::where('restaurant_order_id', $order->id)->count());
    }

    public function test_una_version_antigua_de_pedido_recibe_409(): void
    {
        $order = $this->createOrder('OPEN');
        $this->createItem($order, 'PENDING');

        $response = $this->actingAs($this->user)
            ->postJson("/restaurant/orders/{$order->id}/items", [
                'product_id' => $this->product->id,
                'quantity' => 1,
                'order_version' => 'version-obsoleta',
            ]);

        $response->assertStatus(409);
        $response->assertJsonPath('requires_reload', true);
    }

    public function test_un_pedido_inexistente_responde_404(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/restaurant/orders/999999999');

        $response->assertStatus(404);
        $response->assertJsonPath('not_found', true);
    }

    public function test_la_mesa_devuelve_el_pedido_activo_para_fusionar(): void
    {
        $order = $this->createOrder('SENT_TO_KITCHEN');

        $response = $this->actingAs($this->user)
            ->getJson("/restaurant/tables/{$this->table->id}/order");

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('order.id', $order->id);
    }

    public function test_el_kds_no_muestra_lotes_de_pedidos_terminados(): void
    {
        $order = $this->createOrder('COMPLETED');
        $item = $this->createItem($order, 'SENT');
        $batch = $this->createBatch($order, 'PENDING');
        $item->update(['kitchen_batch_id' => $batch->id]);

        $response = $this->actingAs($this->user)
            ->getJson('/restaurant/kitchen-orders?kds=cocina');

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonCount(0, 'orders');
    }

    public function test_el_tiempo_de_inactividad_tiene_default_y_se_puede_configurar(): void
    {
        $this->assertSame(15, Setting::restaurantIdleTimeout());

        Setting::setRestaurantIdleTimeout(20);

        $this->assertSame(20, Setting::restaurantIdleTimeout());
    }

    public function test_un_usuario_sin_permiso_no_puede_acceder_al_restaurante(): void
    {
        $plainUser = User::factory()->create(['role' => 'user']);

        $this->actingAs($plainUser)
            ->get('/restaurant')
            ->assertForbidden();
    }

    public function test_un_usuario_no_admin_no_puede_gestionar_pisos_ni_mesas(): void
    {
        $plainUser = User::factory()->create(['role' => 'user']);

        $this->actingAs($plainUser)
            ->get('/restaurant/floors')
            ->assertForbidden();
    }

    private function createOrder(string $status): RestaurantOrder
    {
        return RestaurantOrder::create([
            'company_id' => $this->company->id,
            'table_id' => $this->table->id,
            'user_id' => $this->user->id,
            'order_number' => 'P-TEST-' . uniqid(),
            'status' => $status,
            'subtotal' => 10,
            'igv' => 1.53,
            'total' => 11.53,
        ]);
    }

    private function createItem(RestaurantOrder $order, string $status): RestaurantOrderItem
    {
        return RestaurantOrderItem::create([
            'restaurant_order_id' => $order->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->descripcion,
            'quantity' => 1,
            'unit_price' => 10,
            'total' => 10,
            'kitchen_status' => $status,
            'kds_destination' => 'cocina',
        ]);
    }

    private function createBatch(RestaurantOrder $order, string $status): RestaurantKitchenBatch
    {
        return RestaurantKitchenBatch::create([
            'restaurant_order_id' => $order->id,
            'company_id' => $this->company->id,
            'kds_destination' => 'cocina',
            'batch_number' => 1,
            'status' => $status,
            'sent_at' => now()->subMinutes(10),
            'preparing_started_at' => $status === 'PREPARING' ? now()->subMinutes(5) : null,
            'finished_at' => $status === 'DELIVERED' ? now() : null,
            'sent_by_user_id' => $this->user->id,
        ]);
    }
}
