@extends('layouts.app')
@section('title', 'UI Components Demo')

@section('styles')
<style>
  .ui-demo-container { max-width: 900px; margin: 0 auto; padding: 2rem; }
  .demo-section { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: 2rem; margin-bottom: 2rem; box-shadow: 0 4px 6px rgba(0,0,0,0.02); }
  .demo-title { font-size: 1.1rem; font-weight: 700; margin-bottom: 1.5rem; color: var(--txt); border-bottom: 1px solid var(--border); padding-bottom: .5rem; }
  
  /* ── 1. Advanced Date Picker (Bootstrap Custom) ── */
  .custom-datepicker {
      position: relative;
      display: inline-flex;
      align-items: center;
  }
  .custom-datepicker input[type="date"] {
      padding: 0.5rem 1rem 0.5rem 2.5rem;
      border: 1px solid var(--border);
      border-radius: 8px;
      font-size: 0.9rem;
      font-weight: 500;
      color: var(--txt);
      background-color: var(--surface);
      cursor: pointer;
      transition: all 0.2s ease;
  }
  .custom-datepicker input[type="date"]:focus {
      border-color: var(--blue);
      outline: none;
      box-shadow: 0 0 0 3px rgba(37,99,235,0.15);
  }
  .custom-datepicker i {
      position: absolute;
      left: 0.8rem;
      color: var(--txt-3);
      font-size: 1.1rem;
      pointer-events: none;
  }
  /* Sembunyikan icon kalender bawaan browser agar tidak double */
  .custom-datepicker input[type="date"]::-webkit-calendar-picker-indicator {
      background: transparent;
      bottom: 0;
      color: transparent;
      cursor: pointer;
      height: auto;
      left: 0;
      position: absolute;
      right: 0;
      top: 0;
      width: auto;
  }

  /* ── 2. Advanced Dropdown (Nested & Icons) ── */
  .btn-advanced {
      background: var(--surface);
      border: 1px solid var(--border);
      color: var(--txt);
      font-weight: 600;
      padding: 0.5rem 1rem;
      border-radius: 8px;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      transition: all 0.15s;
  }
  .btn-advanced:hover, .btn-advanced[aria-expanded="true"] {
      background: var(--bg);
      border-color: var(--txt-3);
  }
  .adv-dropdown-menu {
      border-radius: 12px;
      border: 1px solid var(--border);
      box-shadow: 0 10px 25px rgba(0,0,0,0.08);
      padding: 0.5rem;
      min-width: 240px;
  }
  .adv-dropdown-item {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      padding: 0.4rem 0.75rem;
      border-radius: 6px;
      font-size: 0.85rem;
      font-weight: 500;
      color: var(--txt);
      transition: background 0.1s;
      cursor: pointer;
  }
  .adv-dropdown-item:hover {
      background: var(--bg);
  }
  .adv-dropdown-item i { font-size: 1.1rem; color: var(--txt-3); }
  .adv-dropdown-item .shortcut { margin-left: auto; color: var(--txt-3); font-size: 0.75rem; font-weight: 600; }
  
  .adv-dropdown-divider { border-top: 1px solid var(--border); margin: 0.5rem 0; }
  
  /* Status Dot */
  .status-dot { display: inline-block; width: 8px; height: 8px; border-radius: 50%; }
  .status-dot.online { background-color: #22c55e; }
  .status-dot.offline { background-color: #94a3b8; }

  /* Submenu CSS */
  .adv-dropdown-submenu { position: relative; }
  .adv-dropdown-submenu > .adv-dropdown-menu {
      top: 0;
      left: 100%;
      margin-top: -6px;
      margin-left: 0.1rem;
      display: none;
      position: absolute;
  }
  .adv-dropdown-submenu:hover > .adv-dropdown-menu { display: block; }
  .adv-dropdown-submenu-caret { margin-left: auto; font-size: 1rem !important; }

  /* Checkbox Dropdown Item */
  .adv-dropdown-item input[type="checkbox"] {
      accent-color: var(--blue);
      width: 16px; height: 16px;
      cursor: pointer;
  }

  /* ── 3. Advanced Pagination ── */
  .adv-pagination-container {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 1rem;
      border: 1px solid var(--border);
      border-radius: 12px;
      background: var(--surface);
  }
  .adv-pagination-info { font-size: 0.85rem; color: var(--txt-2); font-weight: 500; }
  .adv-pagination-controls { display: flex; align-items: center; gap: 0.5rem; }
  .adv-pagination-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 32px; height: 32px;
      border: 1px solid var(--border);
      background: var(--surface);
      color: var(--txt);
      border-radius: 8px;
      cursor: pointer;
      font-size: 0.85rem;
      transition: all 0.15s;
  }
  .adv-pagination-btn:hover { background: var(--bg); border-color: var(--txt-3); }
  .adv-pagination-btn.active { background: var(--blue); color: white; border-color: var(--blue); font-weight: 700; }
  .adv-pagination-btn:disabled { opacity: 0.5; cursor: not-allowed; }
  .adv-page-size-select {
      border: 1px solid var(--border);
      background: var(--surface);
      color: var(--txt);
      border-radius: 8px;
      padding: 0.3rem 0.5rem;
      font-size: 0.8rem;
      font-weight: 600;
      cursor: pointer;
  }
</style>
@endsection

@section('content')
<div class="ui-demo-container">
  
  <div class="d-flex align-items-center mb-4 gap-3">
    <a href="{{ route('admin.dashboard') }}" class="btn btn-light rounded-circle p-2 d-inline-flex"><i class='bx bx-arrow-back'></i></a>
    <h3 class="fw-bold m-0">UI Components Demo (Bootstrap 5)</h3>
  </div>
  <p class="text-muted mb-4">Semua komponen di bawah ini 100% menggunakan <b>Bootstrap 5 + Custom CSS</b> (Tanpa React), dan langsung bisa dicopy-paste ke file `.blade.php` Anda!</p>

  <!-- 1. Dropdown Button Advanced -->
  <div class="demo-section">
    <div class="demo-title"><i class='bx bx-menu-alt-left me-2'></i> 1. Advanced Dropdown (Nested & Checkbox)</div>
    
    <div class="dropdown">
      <button class="btn-advanced" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
        Actions <i class='bx bx-chevron-down'></i>
      </button>
      
      <div class="dropdown-menu adv-dropdown-menu">
        <!-- Section 1: Standard Items -->
        <div class="adv-dropdown-item"><i class='bx bx-left-arrow-alt'></i> Back</div>
        <div class="adv-dropdown-item"><i class='bx bx-right-arrow-alt'></i> Forward</div>
        <div class="adv-dropdown-item"><i class='bx bx-refresh'></i> Reload <span class="shortcut">⌘R</span></div>
        <div class="adv-dropdown-item"><i class='bx bx-edit-alt'></i> Edit page</div>
        <div class="adv-dropdown-item"><i class='bx bx-star'></i> Add to favorites</div>

        <div class="adv-dropdown-divider"></div>

        <!-- Section 2: Checkboxes -->
        <label class="adv-dropdown-item">
          <input type="checkbox" checked> Show bookmarks
        </label>
        <label class="adv-dropdown-item">
          <input type="checkbox"> Show full URLs
        </label>

        <div class="adv-dropdown-divider"></div>

        <!-- Section 3: Status Icons -->
        <div class="adv-dropdown-item">
          <span class="status-dot online"></span> Olivia Rhye
        </div>
        <div class="adv-dropdown-item">
          <span class="status-dot offline"></span> Sienna Hewitt
        </div>

        <div class="adv-dropdown-divider"></div>

        <!-- Section 4: Submenus -->
        <div class="adv-dropdown-submenu">
          <div class="adv-dropdown-item"><i class='bx bx-cube'></i> More tools <i class='bx bx-chevron-right adv-dropdown-submenu-caret'></i></div>
          
          <!-- Nested Menu Level 1 -->
          <div class="dropdown-menu adv-dropdown-menu">
            <div class="adv-dropdown-submenu">
              <div class="adv-dropdown-item"><i class='bx bx-download'></i> Save as <i class='bx bx-chevron-right adv-dropdown-submenu-caret'></i></div>
              <!-- Nested Menu Level 2 -->
              <div class="dropdown-menu adv-dropdown-menu">
                <div class="adv-dropdown-item">PDF Document</div>
                <div class="adv-dropdown-item">HTML Page</div>
                <div class="adv-dropdown-item">Markdown File</div>
              </div>
            </div>
            
            <div class="adv-dropdown-item"><i class='bx bx-cut'></i> Cut <span class="shortcut">⌘X</span></div>
            <div class="adv-dropdown-item"><i class='bx bx-copy'></i> Copy <span class="shortcut">⌘C</span></div>
            
            <div class="adv-dropdown-divider"></div>
            
            <div class="adv-dropdown-submenu">
              <div class="adv-dropdown-item"><i class='bx bx-code-alt'></i> Developer <i class='bx bx-chevron-right adv-dropdown-submenu-caret'></i></div>
              <div class="dropdown-menu adv-dropdown-menu">
                <div class="adv-dropdown-item">View source</div>
                <div class="adv-dropdown-item">Developer tools</div>
                <div class="adv-dropdown-item">Inspect elements</div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- 2. Controlled Date Picker -->
  <div class="demo-section">
    <div class="demo-title"><i class='bx bx-calendar me-2'></i> 2. Controlled Date Picker</div>
    <div class="custom-datepicker">
      <i class='bx bx-calendar'></i>
      <input type="date" value="{{ date('Y-m-d') }}">
    </div>
    <p class="text-muted mt-2 mb-0" style="font-size: 0.8rem;"><i>Desain input kalender yang elegan menyembunyikan icon kalender jelek bawaan browser.</i></p>
  </div>

  <!-- 3. Advanced Pagination Card -->
  <div class="demo-section">
    <div class="demo-title"><i class='bx bx-list-ol me-2'></i> 3. Advanced Pagination Card</div>
    
    <div class="adv-pagination-container">
      <div class="adv-pagination-info">
        Menampilkan <b>1</b> hingga <b>10</b> dari <b>42</b> hasil
      </div>
      
      <div class="d-flex align-items-center gap-3">
        <select class="adv-page-size-select">
          <option value="10">10 per hal</option>
          <option value="20">20 per hal</option>
          <option value="50">50 per hal</option>
        </select>
        
        <div class="adv-pagination-controls">
          <button class="adv-pagination-btn" disabled><i class='bx bx-chevron-left'></i></button>
          <button class="adv-pagination-btn active">1</button>
          <button class="adv-pagination-btn">2</button>
          <button class="adv-pagination-btn">3</button>
          <button class="adv-pagination-btn">4</button>
          <button class="adv-pagination-btn">5</button>
          <button class="adv-pagination-btn"><i class='bx bx-chevron-right'></i></button>
        </div>
      </div>
    </div>
  </div>

</div>
@endsection
