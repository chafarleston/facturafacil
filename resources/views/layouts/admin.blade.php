<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'FacturaIA') - Admin</title>
  
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@300;400;600;700&display=swap">
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ionicons@7.2.1/css/ionicons.min.css">
  
  <!-- AdminLTE CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/css/adminlte.min.css">
  
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/overlayscrollbars/1.14.0/css/OverlayScrollbars.min.css">
  
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
        <span class="brand-text font-weight-light">FacturaIA</span>
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
              <a href="{{ route('companies.index') }}" class="nav-link {{ request()->routeIs('companies.*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-building"></i>
                <p>Empresas</p>
              </a>
            </li>
            
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
              <a href="{{ route('series.index') }}" class="nav-link {{ request()->routeIs('series.*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-list-ol"></i>
                <p>Series</p>
              </a>
            </li>
            
            <li class="nav-item">
              <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-user-cog"></i>
                <p>Usuarios</p>
              </a>
            </li>
            @endcan
            
            <li class="nav-header">COMPROBANTES</li>
            
            <li class="nav-item">
              <a href="{{ route('invoices.index', ['type' => '01']) }}" class="nav-link">
                <i class="nav-icon fas fa-file-invoice"></i>
                <p>Facturas</p>
              </a>
            </li>
            
            <li class="nav-item">
              <a href="{{ route('invoices.index', ['type' => '03']) }}" class="nav-link">
                <i class="nav-icon fas fa-receipt"></i>
                <p>Boletas</p>
              </a>
            </li>
            
            <li class="nav-item">
              <a href="{{ route('invoices.create') }}" class="nav-link">
                <i class="nav-icon fas fa-plus"></i>
                <p>Nuevo Comprobante</p>
              </a>
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
      <strong>FacturaIA &copy; {{ date('Y') }}</strong>
    </footer>
  </div>
  
  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script>window.jQuery || document.write('<script src="{{ asset('plugins/jquery/jquery.min.js') }}"><\/script>')</script>
  
  <!-- Bootstrap 4 -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
  
  <!-- overlayScrollbars -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/overlayscrollbars/1.14.0/js/OverlayScrollbars.min.js"></script>
  
  <!-- AdminLTE App -->
  <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/js/adminlte.min.js"></script>
  
  @stack('scripts')
</body>
</html>