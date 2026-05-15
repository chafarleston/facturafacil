<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'FacturaFacil by RealComputer SAC') - Admin</title>
  
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@300;400;600;700&display=swap">
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <!-- AdminLTE CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/css/adminlte.min.css">
  
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.10.1/styles/overlayscrollbars.min.css">
  <style>
  .main-sidebar .sidebar { overflow-y: auto !important; max-height: calc(100vh - 60px); }
  .main-sidebar .sidebar::-webkit-scrollbar { width: 5px; }
  .main-sidebar .sidebar::-webkit-scrollbar-track { background: transparent; }
  .main-sidebar .sidebar::-webkit-scrollbar-thumb { background: #555; border-radius: 3px; }
  </style>
  
  @stack('styles')
</head>
<body class="hold-transition sidebar-mini sidebar-collapse">
  <div class="wrapper">
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
          <a href="{{ route('dashboard') }}" class="nav-link">Dashboard</a>
        </li>
      </ul>
      
      <ul class="navbar-nav ml-auto">
        <li class="nav-item dropdown">
          <a class="nav-link" data-toggle="dropdown" href="#">
            <i class="far fa-user"></i> {{ Auth::user()->name ?? 'Usuario' }}
          </a>
          <div class="dropdown-menu dropdown-menu-right">
            <a href="{{ route('profile.edit') }}" class="dropdown-item">
              <i class="fas fa-user"></i> Perfil
            </a>
            <div class="dropdown-divider"></div>
            <form method="POST" action="{{ route('logout') }}">
              @csrf
              <button type="submit" class="dropdown-item">
                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
              </button>
            </form>
          </div>
        </li>
      </ul>
    </nav>
    
    <!-- Main Sidebar -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
      <a href="{{ route('dashboard') }}" class="brand-link">
        <i class="fas fa-file-invoice brand-image ml-3 mr-2" style="font-size: 1.5rem;"></i>
        <span class="brand-text font-weight-light">FacturaFacil by RealComputer SAC</span>
      </a>
      
      <div class="sidebar">
        <nav class="mt-2">
          <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
            
            <li class="nav-item">
              <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="nav-icon fas fa-tachometer-alt"></i>
                <p>Dashboard</p>
              </a>
            </li>
            
            @can('admin')
            <li class="nav-item">
              <a href="#" class="nav-link {{ request()->routeIs('companies.*') || request()->routeIs('series.*') || request()->routeIs('users.*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-building"></i>
                <p>
                  Empresas
                  <i class="fas fa-angle-left right"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="{{ route('companies.index') }}" class="nav-link {{ request()->routeIs('companies.*') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Datos de Empresa</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="{{ route('series.index') }}" class="nav-link {{ request()->routeIs('series.*') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Series</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Usuarios</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="{{ route('roles.index') }}" class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Roles</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="{{ route('permissions.index') }}" class="nav-link {{ request()->routeIs('permissions.*') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Permisos</p>
                  </a>
                </li>
              </ul>
            </li>
            @endcan
            
            <li class="nav-item">
              <a href="{{ route('customers.index') }}" class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-users"></i>
                <p>Clientes</p>
              </a>
            </li>
            
            <li class="nav-item">
              <a href="#" class="nav-link {{ request()->routeIs('products.*') || request()->routeIs('categories.*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-box"></i>
                <p>
                  Productos
                  <i class="fas fa-angle-left right"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Lista de Productos</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="{{ route('categories.index') }}" class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Categorías</p>
                  </a>
                </li>
              </ul>
            </li>
            
            <li class="nav-item">
              <a href="#" class="nav-link {{ request()->routeIs('invoices.*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-file-invoice"></i>
                <p>
                  Comprobantes
                  <i class="fas fa-angle-left right"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="{{ route('invoices.index', ['type' => '01']) }}" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Facturas</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="{{ route('invoices.index', ['type' => '03']) }}" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Boletas</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="{{ route('invoices.index', ['type' => 'NV']) }}" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Notas de Venta</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="{{ route('invoices.create') }}" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Nuevo Comprobante</p>
                  </a>
                </li>
              </ul>
            </li>
            
            <li class="nav-item">
              <a href="#" class="nav-link {{ request()->routeIs('purchases.*') || request()->routeIs('suppliers.*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-shopping-cart"></i>
                <p>
                  Compras
                  <i class="fas fa-angle-left right"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="{{ route('purchases.index') }}" class="nav-link {{ request()->routeIs('purchases.index') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Lista de Compras</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="{{ route('purchases.create') }}" class="nav-link {{ request()->routeIs('purchases.create') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Nueva Compra</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="{{ route('suppliers.index') }}" class="nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Proveedores</p>
                  </a>
                </li>
              </ul>
            </li>
            
            <li class="nav-item">
              <a href="{{ route('cashregisters.index') }}" class="nav-link {{ request()->routeIs('cashregisters.*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-cash-register"></i>
                <p>Caja</p>
              </a>
            </li>
            
            <li class="nav-item">
              <a href="{{ route('pos.index') }}" class="nav-link {{ request()->routeIs('pos.*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-shopping-cart"></i>
                <p>Punto de Venta</p>
              </a>
            </li>

            @can('restaurant')
            <li class="nav-item">
              <a href="#" class="nav-link {{ request()->routeIs('restaurant.*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-utensils"></i>
                <p>
                  Restaurante
                  <i class="fas fa-angle-left right"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="{{ route('restaurant.index') }}" class="nav-link {{ request()->routeIs('restaurant.index') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Mesas</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="{{ route('restaurant.kitchen.cocina') }}" class="nav-link" target="_blank">
                    <i class="far fa-circle nav-icon"></i>
                    <p>KDS Cocina</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="{{ route('restaurant.kitchen.cocina2') }}" class="nav-link" target="_blank">
                    <i class="far fa-circle nav-icon"></i>
                    <p>KDS Cocina 2</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="{{ route('restaurant.kitchen.bar') }}" class="nav-link" target="_blank">
                    <i class="far fa-circle nav-icon"></i>
                    <p>KDS Bar</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="{{ route('restaurant.floors.index') }}" class="nav-link {{ request()->routeIs('restaurant.floors.*') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Pisos</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="{{ route('restaurant.mode') }}" class="nav-link {{ request()->routeIs('restaurant.mode') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Modo de Pedidos</p>
                  </a>
                </li>
              </ul>
            </li>
            @endcan

            <li class="nav-item">
              <a href="{{ route('printers.index') }}" class="nav-link {{ request()->routeIs('printers.*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-print"></i>
                <p>Impresoras</p>
              </a>
            </li>

          </ul>
        </nav>
      </div>
    </aside>
    
    <!-- Content Wrapper -->
    <div class="content-wrapper">
      <div class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1 class="m-0">@yield('page_title', 'Dashboard')</h1>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                @yield('breadcrumbs')
              </ol>
            </div>
          </div>
        </div>
      </div>
      
      <section class="content">
        <div class="container-fluid">
          
          @if(session('success'))
          <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('success') }}
          </div>
          @endif
          
          @if(session('error'))
          <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('error') }}
          </div>
          @endif
          
          @yield('content')
          
        </div>
      </section>
    </div>
    
    <footer class="main-footer">
      <div class="float-right d-none d-sm-block">
        <b>Version</b> 1.0
      </div>
      <strong>FacturaFacil by RealComputer SAC &copy; {{ date('Y') }}</strong>
    </footer>
  </div>
  
  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script>window.jQuery || document.write('<script src="{{ asset('plugins/jquery/jquery.min.js') }}"><\/script>')</script>
  
  <!-- Bootstrap 4 -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
  
  <!-- AdminLTE App -->
  <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/js/adminlte.min.js"></script>
  
  <!-- Customer Search Global Function -->
<script>
  // Función global para cargar departamentos al iniciar
  function loadDepartamentosGlobal() {
      var deptSelect = document.getElementById('departamento');
      if (!deptSelect) return;
      
      fetch('/ubigeo/departamentos')
      .then(function(res) { return res.json(); })
      .then(function(data) {
          data.forEach(function(dept) {
              var opt = document.createElement('option');
              opt.value = dept;
              opt.textContent = dept;
              deptSelect.appendChild(opt);
          });
      });
  }
  
  function cleanAddress(direccion) {
      if (!direccion) return '';
      // Reemplazar múltiples "- -" por un solo "-"
      direccion = direccion.replace(/(\s*-\s*)+/g, ' - ');
      // Eliminar espacios extra antes y después
      direccion = direccion.trim();
      // Si termina en "-", removerlo
      if (direccion.endsWith(' -')) {
          direccion = direccion.slice(0, -2).trim();
      }
      return direccion;
  }
  
  function buscarClienteGlobal() {
      var docNumero = document.getElementById('doc_numero').value.trim();
      var companyId = document.querySelector('input[name="company_id"]') ? document.querySelector('input[name="company_id"]').value : 1;
      var statusEl = document.getElementById('customer-status');
      
      if (!docNumero) {
          alert('Ingrese número de documento');
          return;
      }
      
      if (statusEl) {
          statusEl.textContent = 'Buscando...';
          statusEl.className = 'text-sm text-info';
      }
      
      fetch('/decolecta/search?company_id=' + companyId + '&documento=' + docNumero)
      .then(function(res) {
          if (!res.ok) throw new Error('HTTP ' + res.status);
          return res.json();
      })
      .then(function(data) {
          if (statusEl) {
              if (data.found && data.exists) {
                  if (document.getElementById('customer_nombre')) {
                      document.getElementById('customer_nombre').value = data.customer.nombre || '';
                  }
                  if (document.getElementById('customer_direccion')) {
                      document.getElementById('customer_direccion').value = data.customer.direccion || '';
                  }
                  if (document.getElementById('doc_tipo')) {
                      document.getElementById('doc_tipo').value = data.customer.documento_tipo;
                  }
                  statusEl.textContent = '✓ Cliente encontrado';
                  statusEl.className = 'text-sm text-success';
                  if (data.customer && data.customer.ubigeo) {
                      loadUbigeoFromCode(data.customer.ubigeo);
                  }
              } else if (data.api_data) {
                  if (document.getElementById('customer_nombre')) {
                      document.getElementById('customer_nombre').value = data.api_data.nombre || '';
                  }
                  if (document.getElementById('customer_direccion')) {
                      document.getElementById('customer_direccion').value = cleanAddress(data.api_data.direccion) || '';
                  }
                  statusEl.textContent = 'Datos cargados desde SUNAT';
                  statusEl.className = 'text-sm text-warning';
                  if (data.api_data && data.api_data.ubigeo) {
                      loadUbigeoFromCode(data.api_data.ubigeo);
                  }
              } else {
                  statusEl.textContent = 'Cliente no encontrado';
                  statusEl.className = 'text-sm text-danger';
              }
          }
      })
      .catch(function(err) {
          if (statusEl) {
              statusEl.textContent = 'Error al buscar';
              statusEl.className = 'text-sm text-danger';
          }
      });
  }
  
  function loadUbigeoFromCode(codigo) {
    if (!codigo) return;
    
    var deptSelect = document.getElementById('departamento');
    var provSelect = document.getElementById('provincia');
    var distSelect = document.getElementById('distrito');
    
    if (!deptSelect || !provSelect || !distSelect) return;
    
    provSelect.disabled = false;
    distSelect.disabled = false;
    
    var xhr = new XMLHttpRequest();
    xhr.open('GET', '/ubigeo/departamentos', false);
    xhr.send('');
    
    if (xhr.status === 200) {
        var deptData = JSON.parse(xhr.responseText);
        deptSelect.innerHTML = '<option value="">Seleccionar</option>';
        deptData.forEach(function(dept) {
            var opt = document.createElement('option');
            opt.value = dept;
            opt.textContent = dept;
            deptSelect.appendChild(opt);
        });
        
        var xhr2 = new XMLHttpRequest();
        xhr2.open('GET', '/ubigeo/by-codigo?codigo=' + codigo, false);
        xhr2.send('');
        
        if (xhr2.status === 200) {
            var ubigeoData = JSON.parse(xhr2.responseText);
            if (ubigeoData && ubigeoData.departamento) {
                deptSelect.value = ubigeoData.departamento;
                loadProvinciasForUbigeo(ubigeoData.departamento, ubigeoData.provincia, ubigeoData.distrito);
            }
        }
    }
  }

  function loadProvinciasForUbigeo(dept, selectedProv, selectedDist) {
    var provSelect = document.getElementById('provincia');
    if (!provSelect) return;
    
    if (document.getElementById('distrito')) {
        document.getElementById('distrito').disabled = false;
    }
    
    var xhr = new XMLHttpRequest();
    xhr.open('GET', '/ubigeo/provincias?departamento=' + encodeURIComponent(dept), false);
    xhr.send('');
    
    if (xhr.status === 200) {
        var provData = JSON.parse(xhr.responseText);
        provSelect.innerHTML = '<option value="">Seleccionar</option>';
        provSelect.disabled = false;
        provData.forEach(function(prov) {
            var opt = document.createElement('option');
            opt.value = prov;
            opt.textContent = prov;
            provSelect.appendChild(opt);
        });
        
        if (selectedProv) {
            provSelect.value = selectedProv;
            loadDistritosForUbigeo(dept, selectedProv, selectedDist);
        }
    }
  }

  function loadDistritosForUbigeo(dept, prov, selectedDist) {
    var distSelect = document.getElementById('distrito');
    if (!distSelect) return;
    
    var xhr = new XMLHttpRequest();
    xhr.open('GET', '/ubigeo/distritos?departamento=' + encodeURIComponent(dept) + '&provincia=' + encodeURIComponent(prov), false);
    xhr.send('');
    
    if (xhr.status === 200) {
        var distData = JSON.parse(xhr.responseText);
        distSelect.innerHTML = '<option value="">Seleccionar</option>';
        distSelect.disabled = false;
        
        var matched = false;
        distData.forEach(function(d) {
            var opt = document.createElement('option');
            opt.value = d.codigo;
            opt.textContent = d.distrito;
            opt.dataset.distrito = d.distrito;
            distSelect.appendChild(opt);
            
            if (d.distrito.toUpperCase() === selectedDist.toUpperCase()) {
                distSelect.value = d.codigo;
                matched = true;
            }
        });
        
        if (!matched && selectedDist) {
            distData.forEach(function(d) {
                if (d.codigo === selectedDist) {
                    distSelect.value = d.codigo;
                }
            });
        }
        
if (document.getElementById('ubigeo_codigo')) {
            document.getElementById('ubigeo_codigo').value = distSelect.value;
      }
    }
  }
  </script>
  
  @stack('scripts')
</body>
</html>