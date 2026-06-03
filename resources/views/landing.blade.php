<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>NETKING</title>
  <meta name="description" content="Cari internet rumah unlimited tanpa FUP? Netking solusinya! Mulai Rp100rb/bln, jaringan mandiri stabil, anti-lelet, teknisi tanggap &lt;4 jam. Temukan paketmu.">
  <meta name="keywords" content="internet rumah murah, pasang wifi rumah, internet tanpa batas kuota, internet cepat stabil, internet bulanan murah, Netking">
  <meta name="robots" content="index, follow">
  <link rel="canonical" href="{{ url('/') }}">
  <meta property="og:type" content="website">
  <meta property="og:url" content="{{ url('/') }}">
  <meta property="og:title" content="Netking: Solusi Cerdas Internet Rumah Murah &amp; Cepat Mulai Rp100rb">
  <meta property="og:description" content="Lelah kuota habis dan jaringan lelet? Beralih ke Netking. Internet murni tanpa batasan, harga transparan, dan penanganan tanggap di bawah 4 jam.">
  <meta property="og:image" content="{{ asset('img/NetkingLoginBaruLight.png') }}">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Netking: Solusi Cerdas Internet Rumah Murah &amp; Cepat Mulai Rp100rb">
  <meta name="twitter:description" content="Lelah kuota habis dan jaringan lelet? Beralih ke Netking. Internet murni tanpa batasan, harga transparan, dan penanganan tanggap di bawah 4 jam.">
  <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
  <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
  <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('favicon-96x96.png') }}">
  <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
  <link rel="manifest" href="{{ asset('site.webmanifest') }}">
  <script type="application/ld+json">
  {"@context":"https://schema.org","@type":"LocalBusiness","@id":"{{ url('/') }}","name":"Netking","description":"Penyedia layanan internet untuk segmen residensial dan bisnis. Jaringan milik sendiri, monitoring 24/7, teknisi lokal.","url":"{{ url('/') }}","logo":"{{ asset('img/NetkingLoginBaruLight.png') }}","priceRange":"Rp 100.000 – Rp 150.000","serviceType":"Layanan Internet Rumah","areaServed":"Indonesia"}
  </script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    :root{
      --blue:#2563eb;--blue-d:#1d4ed8;--blue-l:#eff6ff;--blue-b:#bfdbfe;
      --bg:#f8fafc;--surface:#ffffff;--border:#e2e8f0;--border-h:#cbd5e1;
      --txt:#1e293b;--txt2:#64748b;--txt3:#94a3b8;
      --radius:8px;--shadow:0 1px 3px rgba(0,0,0,.08);
      --shadow-md:0 4px 12px rgba(0,0,0,.1);--shadow-lg:0 12px 32px rgba(0,0,0,.12);
    }
    html{scroll-behavior:smooth}
    body{font-family:'Inter',system-ui,sans-serif;background:var(--bg);color:var(--txt);-webkit-font-smoothing:antialiased;overflow-x:hidden}

    /* SCROLL PROGRESS */
    #scroll-prog{position:fixed;top:0;left:0;width:0;height:2px;background:var(--blue);z-index:200;transition:width .1s linear}

    /* NAV */
    nav{position:fixed;inset:0 0 auto 0;z-index:99;height:72px;display:flex;align-items:center;justify-content:space-between;padding:0 5%;background:rgba(255,255,255,.92);backdrop-filter:blur(12px);border-bottom:1px solid var(--border);transition:box-shadow .2s}
    nav.scrolled{box-shadow:0 2px 16px rgba(0,0,0,.08)}
    .nav-logo{display:flex;align-items:center;text-decoration:none}
    .nav-logo-text{font-size:1.1rem;font-weight:900;letter-spacing:.08em;color:var(--txt);text-transform:uppercase}
    .nav-links{display:flex;gap:2rem}
    .nav-links a{font-size:.875rem;font-weight:500;color:var(--txt2);text-decoration:none;transition:color .15s;position:relative}
    .nav-links a::after{content:'';position:absolute;bottom:-4px;left:0;width:0;height:2px;background:var(--blue);border-radius:100px;transition:width .2s}
    .nav-links a.active,.nav-links a:hover{color:var(--blue)}
    .nav-links a.active::after,.nav-links a:hover::after{width:100%}
    .nav-burger{display:none;flex-direction:column;gap:5px;cursor:pointer;padding:.4rem;background:none;border:none}
    .nav-burger span{display:block;width:22px;height:2px;background:var(--txt);border-radius:100px;transition:transform .2s,opacity .2s}
    .nav-burger.open span:nth-child(1){transform:translateY(7px) rotate(45deg)}
    .nav-burger.open span:nth-child(2){opacity:0}
    .nav-burger.open span:nth-child(3){transform:translateY(-7px) rotate(-45deg)}
    @media(max-width:680px){
      .nav-links{display:none;position:fixed;top:72px;left:0;right:0;background:#fff;flex-direction:column;padding:1.25rem 5%;gap:0;border-bottom:1px solid var(--border);box-shadow:var(--shadow-md)}
      .nav-links.open{display:flex}
      .nav-links a{padding:.75rem 0;border-bottom:1px solid var(--border)}
      .nav-links a:last-child{border-bottom:none}
      .nav-burger{display:flex}
    }

    /* BUTTONS */
    .btn-blue{display:inline-flex;align-items:center;gap:.45rem;padding:.72rem 1.5rem;background:var(--blue);color:#fff;font-weight:600;font-size:.9rem;border-radius:var(--radius);text-decoration:none;transition:background .15s,box-shadow .15s,transform .1s;border:none;cursor:pointer;white-space:nowrap}
    .btn-blue:hover{background:var(--blue-d);box-shadow:0 4px 12px rgba(37,99,235,.3);transform:translateY(-1px);color:#fff}
    .btn-plain{display:inline-flex;align-items:center;gap:.45rem;padding:.72rem 1.5rem;background:var(--surface);color:var(--txt);font-weight:600;font-size:.9rem;border-radius:var(--radius);text-decoration:none;border:1px solid var(--border);transition:border-color .15s,background .15s,transform .1s;white-space:nowrap}
    .btn-plain:hover{border-color:var(--border-h);background:var(--bg);transform:translateY(-1px);color:var(--txt)}

    /* COVERAGE ACCORDION */
    .coverage-sec{background:var(--surface);border-top:1px solid var(--border);border-bottom:1px solid var(--border);font-family:'Inter',system-ui,sans-serif}
    .coverage-wrap{max-width:860px;margin:0 auto;display:grid;grid-template-columns:1fr;gap:1rem}
    .coverage-top{display:flex;align-items:flex-start;justify-content:space-between;gap:.9rem;flex-wrap:wrap}
    .coverage-pro{display:inline-flex;align-items:center;gap:.35rem;font-size:.73rem;font-weight:700;color:#1d4ed8;background:#eff6ff;border:1px solid #bfdbfe;border-radius:999px;padding:.28rem .68rem}
    .coverage-helper{font-size:.8rem;color:var(--txt3);margin-top:.45rem}
    .cov-accordion{display:flex;flex-direction:column;gap:.7rem}
    .cov-item{border:1px solid var(--border);border-radius:14px;background:#fff;transition:border-color .2s,box-shadow .2s,transform .18s}
    .cov-item:hover{border-color:var(--blue-b);box-shadow:var(--shadow-md);transform:translateY(-1px)}
    .cov-item.open{border-color:#93c5fd;box-shadow:0 0 0 2px rgba(37,99,235,.08),var(--shadow-md)}
    .cov-trigger{width:100%;background:none;border:none;padding:.85rem .95rem;display:flex;align-items:center;justify-content:space-between;gap:.8rem;cursor:pointer;text-align:left;font:inherit;color:inherit}
    .cov-left{display:flex;align-items:center;gap:.6rem;min-width:0}
    .cov-accent{width:4px;height:28px;border-radius:999px;background:transparent;transition:background .22s,box-shadow .22s}
    .cov-item.open .cov-accent{background:var(--blue);box-shadow:0 0 0 4px rgba(37,99,235,.12)}
    .cov-name{font-size:.95rem;font-weight:800;color:var(--txt)}
    .cov-meta{display:flex;align-items:center;gap:.55rem}
    .cov-chip{display:inline-flex;align-items:center;gap:.3rem;font-size:.72rem;font-weight:700;color:#166534;background:#ecfdf3;border:1px solid #bbf7d0;border-radius:999px;padding:.24rem .58rem}
    .cov-chip.soon{color:#92400e;background:#fffbeb;border-color:#fde68a}
    .cov-chip i{font-size:.76rem}
    .cov-chevron{font-size:1rem;color:var(--txt3);transition:transform .2s,color .2s}
    .cov-item.open .cov-chevron{transform:rotate(180deg);color:var(--blue)}
    .cov-panel{display:grid;grid-template-rows:0fr;transition:grid-template-rows .24s ease}
    .cov-item.open .cov-panel{grid-template-rows:1fr}
    .cov-panel-inner{overflow:hidden}
    .cov-content{padding:0 .95rem .9rem}
    .cov-grid{display:flex;flex-wrap:wrap;gap:.5rem}
    .cov-tag{display:inline-flex;align-items:center;gap:.35rem;font-size:.8rem;color:var(--txt2);background:var(--bg);border:1px solid var(--border);border-radius:999px;padding:.42rem .66rem}
    .cov-tag i{font-size:.86rem;color:var(--blue)}
    .cov-note{font-size:.78rem;color:var(--txt3);margin-top:.75rem}
    .cov-cta{margin-top:.8rem}
    .cov-cta .btn-wa-light{background:#16a34a;border:1px solid #15803d;color:#fff;padding:.58rem 1rem;box-shadow:0 6px 14px rgba(21,128,61,.22)}
    .cov-cta .btn-wa-light:hover{background:#15803d;transform:translateY(-1px)}
    @media(max-width:860px){
      .coverage-wrap{max-width:100%}
      .cov-name{font-size:.9rem}
      .cov-trigger{padding:.8rem .78rem}
      .cov-content{padding:0 .78rem .8rem}
    }

    /* HERO */
    .hero{min-height:100vh;padding:96px 5% 64px;display:grid;grid-template-columns:1fr 1fr;gap:4rem;align-items:center;position:relative;overflow:hidden;background:var(--surface);border-bottom:1px solid var(--border)}
    .hero-blob{position:absolute;top:-80px;right:-80px;width:520px;height:520px;background:radial-gradient(circle at 40% 40%,#dbeafe 0%,#eff6ff 60%,transparent 80%);border-radius:60% 40% 30% 70%/60% 30% 70% 40%;animation:blob 10s ease-in-out infinite;z-index:0}
    @keyframes blob{0%,100%{border-radius:60% 40% 30% 70%/60% 30% 70% 40%;transform:scale(1) rotate(0deg)}33%{border-radius:30% 60% 70% 40%/50% 60% 30% 60%;transform:scale(1.05) rotate(4deg)}66%{border-radius:50% 50% 30% 70%/40% 60% 40% 60%;transform:scale(.98) rotate(-3deg)}}
    .hero>.reveal,.hero>.hero-visual{position:relative;z-index:1}
    .hero-tag{display:inline-flex;align-items:center;gap:.4rem;font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--blue);background:var(--blue-l);border:1px solid var(--blue-b);border-radius:100px;padding:.28rem .75rem;margin-bottom:.75rem}
    .hero-tag-dot{width:5px;height:5px;background:var(--blue);border-radius:50%;animation:pulse-dot 2s infinite}
    @keyframes pulse-dot{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.5;transform:scale(.7)}}

    /* OLT BADGE — sebagai trust item */
    .olt-trust{display:flex;align-items:center;gap:.4rem;font-size:.8rem;color:#15803d;font-weight:600;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:100px;padding:.18rem .65rem .18rem .4rem}
    .olt-badge-dot{position:relative;width:8px;height:8px;flex-shrink:0}
    .olt-badge-dot::before{content:'';position:absolute;inset:0;background:#22c55e;border-radius:50%;animation:olt-ping 1.6s ease-out infinite}
    .olt-badge-dot::after{content:'';position:absolute;inset:1.5px;background:#16a34a;border-radius:50%}
    @keyframes olt-ping{0%{transform:scale(1);opacity:.85}100%{transform:scale(2.4);opacity:0}}
    @keyframes count-up{from{opacity:0;transform:translateY(4px)}to{opacity:1;transform:translateY(0)}}
    h1{font-size:clamp(2.2rem,4vw,3.4rem);font-weight:900;line-height:1.1;letter-spacing:-.03em;color:var(--txt)}
    h1 em{font-style:normal;color:var(--blue)}
    .hero-desc{margin-top:1.2rem;color:var(--txt2);font-size:1.05rem;line-height:1.74;max-width:430px}
    .hero-cta{display:flex;gap:.75rem;margin-top:2rem;flex-wrap:wrap}
    .btn-hero-primary{display:inline-flex;align-items:center;gap:.5rem;padding:.82rem 1.6rem;background:var(--blue);color:#fff;border-radius:var(--radius);font-size:.95rem;font-weight:700;text-decoration:none;transition:background .15s,box-shadow .15s,transform .1s;box-shadow:0 4px 14px rgba(37,99,235,.3)}
    .btn-hero-primary:hover{background:var(--blue-d);box-shadow:0 6px 20px rgba(37,99,235,.4);transform:translateY(-2px)}
    .btn-hero-primary i{font-size:1.1rem}
    .hero-trust{display:flex;align-items:center;gap:1.25rem;margin-top:1.5rem;flex-wrap:wrap}
    .hero-trust-item{display:flex;align-items:center;gap:.35rem;font-size:.8rem;color:var(--txt3)}
    .hero-trust-item i{color:#16a34a;font-size:.9rem}
    .hero-visual{display:flex;justify-content:center;align-items:center;position:relative}

    /* PHONE MOCKUP */
    .phone-wrap{position:relative}
    .phone-float{animation:float 4s ease-in-out infinite}
    @keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-12px)}}
    .phone-wrap::before{content:'';position:absolute;left:-3px;top:88px;width:3px;height:32px;background:#334155;border-radius:3px 0 0 3px;box-shadow:0 40px 0 #334155}
    .phone-wrap::after{content:'';position:absolute;right:-3px;top:110px;width:3px;height:52px;background:#334155;border-radius:0 3px 3px 0}
    .phone-outer{width:280px;background:linear-gradient(160deg,#293548 0%,#1a2535 100%);border-radius:44px;padding:12px;box-shadow:0 0 0 1px rgba(255,255,255,.06) inset,0 0 0 1px rgba(0,0,0,.25),0 36px 72px rgba(15,23,42,.35),0 8px 24px rgba(15,23,42,.2)}
    .phone-screen{background:#f1f5f9;border-radius:33px;overflow:hidden;position:relative}
    .ps-statusbar{background:var(--blue);height:22px;display:flex;align-items:center;justify-content:space-between;padding:0 .9rem 0 1rem;position:relative}
    .ps-statusbar::after{content:'';position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:9px;height:9px;background:#0f172a;border-radius:50%;box-shadow:0 0 0 1.5px rgba(255,255,255,.08)}
    .ps-time{font-size:.52rem;font-weight:700;color:#fff}
    .ps-icons{display:flex;gap:.25rem;align-items:center}
    .ps-icons i{font-size:.65rem;color:rgba(255,255,255,.9)}
    .ps-header{background:var(--blue);padding:.7rem 1rem 1rem}
    .ps-header-greet{font-size:.6rem;color:rgba(255,255,255,.72)}
    .ps-header-name{font-size:.95rem;font-weight:800;color:#fff;margin-top:.08rem;letter-spacing:-.01em}
    .ps-header-row{display:flex;align-items:center;justify-content:space-between;margin-top:.5rem}
    .ps-online-pill{display:inline-flex;align-items:center;gap:.28rem;background:rgba(255,255,255,.18);border-radius:100px;padding:.18rem .55rem;font-size:.58rem;font-weight:600;color:#fff}
    .ps-online-dot{width:5px;height:5px;background:#4ade80;border-radius:50%;flex-shrink:0;animation:pulse-dot 2s infinite}
    .ps-notif{width:26px;height:26px;background:rgba(255,255,255,.15);border-radius:8px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:.85rem}
    .ps-bill-wrap{padding:0 .75rem;margin-top:-.5rem}
    .ps-bill{background:#fff;border-radius:14px;padding:.9rem;box-shadow:0 4px 16px rgba(15,23,42,.12)}
    .ps-bill-lbl{font-size:.57rem;color:var(--txt3);font-weight:700;text-transform:uppercase;letter-spacing:.04em}
    .ps-bill-amt{font-size:1.4rem;font-weight:900;color:var(--txt);margin-top:.12rem;letter-spacing:-.02em}
    .ps-bill-due{font-size:.57rem;color:var(--txt2);margin-top:.18rem}
    .ps-bill-btn{display:inline-flex;align-items:center;gap:.3rem;margin-top:.65rem;background:var(--blue);color:#fff;border-radius:8px;font-size:.64rem;font-weight:700;padding:.38rem .85rem}
    .ps-menu{display:grid;grid-template-columns:repeat(3,1fr);gap:.5rem;padding:.75rem}
    .ps-menu-item{background:#fff;border-radius:12px;padding:.7rem .4rem;text-align:center;box-shadow:0 1px 4px rgba(15,23,42,.08)}
    .ps-menu-icon{font-size:1.1rem;color:var(--blue)}
    .ps-menu-lbl{font-size:.57rem;color:var(--txt2);margin-top:.22rem;font-weight:500}
    .ps-rows{background:#fff;border-radius:14px;margin:0 .75rem .75rem;padding:.7rem .85rem;box-shadow:0 1px 4px rgba(15,23,42,.08)}
    .ps-rows-ttl{font-size:.58rem;font-weight:700;color:var(--txt);margin-bottom:.5rem}
    .ps-row{display:flex;align-items:center;justify-content:space-between;padding:.38rem 0;border-bottom:1px solid #f1f5f9;font-size:.62rem}
    .ps-row:last-child{border-bottom:none}
    .ps-row-k{color:var(--txt2)}
    .ps-row-v{font-weight:700;color:var(--txt)}
    .ps-row-v.ok{color:#16a34a}
    .ps-bottomnav{background:#fff;border-top:1px solid #e2e8f0;display:grid;grid-template-columns:repeat(4,1fr);padding:.45rem 0 .55rem}
    .ps-bn-item{display:flex;flex-direction:column;align-items:center;gap:.18rem}
    .ps-bn-item i{font-size:.95rem;color:var(--txt3)}
    .ps-bn-item.active i{color:var(--blue)}
    .ps-bn-item span{font-size:.5rem;color:var(--txt3);font-weight:500}
    .ps-bn-item.active span{color:var(--blue);font-weight:700}
    .ps-bn-dot{width:4px;height:4px;background:var(--blue);border-radius:50%;margin-top:.1rem}
    .float-card{position:absolute;left:-40px;bottom:60px;width:195px;background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:.875rem;box-shadow:var(--shadow-lg)}
    .fc-top{display:flex;align-items:center;gap:.5rem;margin-bottom:.6rem}
    .fc-icon{width:28px;height:28px;background:var(--blue-l);border:1px solid var(--blue-b);border-radius:7px;display:flex;align-items:center;justify-content:center;color:var(--blue);font-size:.85rem;flex-shrink:0}
    .fc-title{font-size:.7rem;font-weight:700;color:var(--txt)}
    .fc-sub{font-size:.6rem;color:var(--txt3)}
    .fc-bar-bg{height:5px;background:var(--border);border-radius:100px;overflow:hidden}
    .fc-bar-fill{height:100%;background:#16a34a;border-radius:100px;width:80%}
    .fc-meta{display:flex;justify-content:space-between;font-size:.57rem;color:var(--txt3);margin-top:.28rem}

    /* PHONE AUTO-SLIDE */
    .ps-slide{position:absolute;inset:0;opacity:0;transition:opacity .6s ease;pointer-events:none;background:#f1f5f9;overflow:hidden}
    .ps-slide.active{opacity:1;pointer-events:auto;position:relative}
    .phone-screen{position:relative;min-height:490px}
    .slide-dots{display:flex;justify-content:center;gap:.4rem;margin-top:.65rem}
    .slide-dot{width:6px;height:6px;border-radius:50%;background:var(--border);transition:background .3s,transform .3s;cursor:pointer}
    .slide-dot.active{background:var(--blue);transform:scale(1.25)}

    /* SPEED TEST SLIDE */
    .sp-wrap{padding:.75rem .85rem}
    .sp-title{font-size:.62rem;font-weight:700;color:var(--txt);margin-bottom:.7rem;text-align:center;letter-spacing:.03em;text-transform:uppercase}
    .sp-gauge-wrap{display:flex;justify-content:center;margin-bottom:.65rem}
    .sp-gauge{position:relative;width:110px;height:60px;overflow:hidden}
    .sp-gauge svg{width:110px;height:110px;transform:translateY(-50px)}
    .sp-num{text-align:center;font-size:1.55rem;font-weight:900;color:var(--blue);line-height:1;letter-spacing:-.03em}
    .sp-unit{text-align:center;font-size:.58rem;color:var(--txt3);margin-top:.12rem;font-weight:600}
    .sp-row2{display:grid;grid-template-columns:1fr 1fr;gap:.45rem;margin:.7rem 0}
    .sp-stat{background:#fff;border-radius:10px;padding:.55rem .65rem;box-shadow:0 1px 4px rgba(15,23,42,.08)}
    .sp-stat-lbl{font-size:.52rem;color:var(--txt3);text-transform:uppercase;letter-spacing:.04em;font-weight:700}
    .sp-stat-val{font-size:.88rem;font-weight:800;color:var(--txt);margin-top:.15rem}
    .sp-stat-val span{font-size:.55rem;font-weight:500;color:var(--txt3)}
    .sp-ping{background:#fff;border-radius:10px;padding:.55rem .75rem;box-shadow:0 1px 4px rgba(15,23,42,.08);display:flex;justify-content:space-between;align-items:center}
    .sp-ping-lbl{font-size:.58rem;color:var(--txt2)}
    .sp-ping-val{font-size:.78rem;font-weight:800;color:#16a34a}
    .sp-badge{display:inline-flex;align-items:center;gap:.25rem;background:#dcfce7;color:#16a34a;font-size:.6rem;font-weight:700;border-radius:100px;padding:.2rem .6rem;margin-top:.55rem}

    /* STATUS SLIDE */
    .st-wrap{padding:.75rem .85rem}
    .st-hdr{display:flex;justify-content:space-between;align-items:center;margin-bottom:.7rem}
    .st-hdr-t{font-size:.62rem;font-weight:700;color:var(--txt);text-transform:uppercase;letter-spacing:.03em}
    .st-online-pill{display:inline-flex;align-items:center;gap:.25rem;background:#dcfce7;color:#16a34a;border-radius:100px;padding:.15rem .5rem;font-size:.55rem;font-weight:700}
    .st-online-dot{width:5px;height:5px;background:#22c55e;border-radius:50%;animation:pulse-dot 2s infinite}
    .st-cards{display:grid;grid-template-columns:1fr 1fr;gap:.45rem;margin-bottom:.5rem}
    .st-card{background:#fff;border-radius:12px;padding:.65rem .7rem;box-shadow:0 1px 4px rgba(15,23,42,.08)}
    .st-card-ico{font-size:1.1rem;color:var(--blue);margin-bottom:.3rem}
    .st-card-val{font-size:1rem;font-weight:900;color:var(--txt);line-height:1;letter-spacing:-.02em}
    .st-card-lbl{font-size:.52rem;color:var(--txt3);margin-top:.18rem}
    .st-bar-wrap{background:#fff;border-radius:12px;padding:.65rem .75rem;box-shadow:0 1px 4px rgba(15,23,42,.08);margin-bottom:.5rem}
    .st-bar-hdr{display:flex;justify-content:space-between;margin-bottom:.45rem}
    .st-bar-lbl{font-size:.58rem;color:var(--txt2);font-weight:600}
    .st-bar-val{font-size:.58rem;font-weight:800;color:#16a34a}
    .st-bar-bg{height:7px;background:#e2e8f0;border-radius:100px;overflow:hidden}
    .st-bar-fill{height:100%;border-radius:100px;background:linear-gradient(90deg,var(--blue),#60a5fa);animation:bar-grow 1.2s ease both}
    @keyframes bar-grow{from{width:0}}
    @media(max-width:860px){
      .hero{grid-template-columns:1fr;padding-top:82px;text-align:center}
      .hero-blob{display:none}
      .hero-visual{margin-top:2rem;order:-1}
      .hero-visual .float-card{display:none}
      .hero-tag,.olt-trust,.hero-cta{justify-content:center}
      .hero-trust{justify-content:center}
      .hero-desc{max-width:100%;margin-left:auto;margin-right:auto}
      .float-card{display:none}
      .phone-outer{width:230px}
    }

    /* SECTION COMMONS */
    section{padding:5rem 5%}
    @media(max-width:680px){section{padding:3rem 5%}}
    .container{max-width:1060px;margin:0 auto}
    .eyebrow{font-size:.7rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--blue);margin-bottom:.875rem}
    h2{font-size:clamp(1.65rem,3.2vw,2.4rem);font-weight:800;line-height:1.18;letter-spacing:-.02em;color:var(--txt)}
    .body-txt{color:var(--txt2);font-size:.975rem;line-height:1.76;margin-top:.9rem}
    .split{display:grid;grid-template-columns:1fr 1fr;gap:5rem;align-items:center;max-width:1060px;margin:0 auto}
    @media(max-width:800px){.split{grid-template-columns:1fr;gap:2.5rem}}

    /* WHY */
    .why-sec{background:var(--surface);border-top:1px solid var(--border);border-bottom:1px solid var(--border)}
    .check-list{list-style:none;margin-top:1.6rem;display:flex;flex-direction:column;gap:.8rem}
    .check-list li{display:flex;align-items:flex-start;gap:.65rem;font-size:.92rem;color:var(--txt2)}
    .check-list li i{color:#16a34a;font-size:1rem;flex-shrink:0;margin-top:.12rem}
    .kpi-grid{display:grid;grid-template-columns:1fr 1fr;gap:.875rem}
    @media(max-width:480px){.kpi-grid{grid-template-columns:1fr}}
    .kpi-card{background:var(--bg);border:1px solid var(--border);border-radius:10px;padding:1.35rem 1.1rem;transition:border-color .2s,box-shadow .2s,transform .2s}
    .kpi-card:hover{border-color:var(--blue-b);box-shadow:var(--shadow-md);transform:translateY(-2px)}
    .kpi-num{font-size:1.9rem;font-weight:900;color:var(--blue);line-height:1}
    .kpi-lbl{font-size:.8rem;color:var(--txt2);margin-top:.35rem;line-height:1.45}

    /* PRICING */
    .pricing-sec{background:var(--bg);border-top:1px solid var(--border);border-bottom:1px solid var(--border)}
    .pricing-head{text-align:center;max-width:560px;margin:0 auto 3rem}
    .pricing-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:1.25rem;max-width:860px;margin:0 auto}
    .price-card{background:var(--surface);border:1.5px solid var(--border);border-radius:14px;padding:1.75rem 1.5rem;text-align:center;position:relative;transition:border-color .2s,box-shadow .2s,transform .2s}
    .price-card:hover{border-color:var(--blue-b);box-shadow:var(--shadow-lg);transform:translateY(-4px)}
    .price-card.popular{border-color:var(--blue);box-shadow:0 0 0 3px rgba(37,99,235,.08),var(--shadow-md)}
    .price-card.popular:hover{transform:translateY(-6px)}
    .price-popular-badge{position:absolute;top:-12px;left:50%;transform:translateX(-50%);color:#fff;font-size:.65rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;padding:.22rem .85rem;border-radius:100px;white-space:nowrap;background-size:300% 100%;background-image:linear-gradient(90deg,var(--blue),var(--blue-d),#3b82f6,var(--blue-d),var(--blue));animation:shimmer-badge 3s linear infinite}
    .price-terlaris-badge{position:absolute;top:-12px;left:50%;transform:translateX(-50%);color:#fff;font-size:.65rem;font-weight:700;padding:.22rem .9rem;border-radius:100px;white-space:nowrap;background:#f97316;box-shadow:0 2px 8px rgba(249,115,22,.4)}
    @keyframes shimmer-badge{0%{background-position:100% 50%}100%{background-position:0% 50%}}
    .price-speed{font-size:2.4rem;font-weight:900;color:var(--blue);line-height:1}
    .price-unit{font-size:.75rem;font-weight:600;color:var(--txt3);margin-top:.2rem}
    .price-divider{height:1px;background:var(--border);margin:1.1rem 0}
    .price-amount{font-size:1.6rem;font-weight:900;color:var(--txt);letter-spacing:-.02em}
    .price-period{font-size:.75rem;color:var(--txt3);margin-top:.15rem}
    .price-feats{list-style:none;margin-top:1.2rem;display:flex;flex-direction:column;gap:.55rem;text-align:left}
    .price-feats li{display:flex;align-items:center;gap:.5rem;font-size:.83rem;color:var(--txt2)}
    .price-feats li i{color:#16a34a;font-size:.9rem;flex-shrink:0}
    .price-card .btn-blue,.price-card .btn-plain{margin-top:1.5rem;width:100%;justify-content:center}
    @media(max-width:680px){.pricing-grid{grid-template-columns:1fr;max-width:420px}}

    /* CUSTOMER APP */
    .feat-list{list-style:none;margin-top:1.75rem;display:flex;flex-direction:column;gap:1.2rem}
    .feat-list li{display:flex;gap:.9rem;align-items:flex-start}
    .feat-icon{width:34px;height:34px;background:var(--blue-l);border:1px solid var(--blue-b);border-radius:8px;display:flex;align-items:center;justify-content:center;color:var(--blue);font-size:.95rem;flex-shrink:0}
    .feat-t strong{display:block;font-size:.88rem;font-weight:700;margin-bottom:.18rem;color:var(--txt)}
    .feat-t span{font-size:.83rem;color:var(--txt2);line-height:1.58}

    /* PARTNER */
    .partner-sec{background:var(--surface);border-top:1px solid var(--border);border-bottom:1px solid var(--border)}
    .laptop{background:var(--surface);border:1px solid var(--border);border-radius:10px;overflow:hidden;box-shadow:var(--shadow-lg)}
    .lt-bar{height:30px;background:#f1f5f9;border-bottom:1px solid var(--border);display:flex;align-items:center;padding:0 .875rem;gap:.38rem}
    .ld{width:8px;height:8px;border-radius:50%}
    .ld-r{background:#fca5a5}.ld-y{background:#fcd34d}.ld-g{background:#6ee7b7}
    .lt-body{display:grid;grid-template-columns:144px 1fr;min-height:250px}
    .lt-side{background:var(--bg);border-right:1px solid var(--border);padding:.75rem .65rem}
    .lt-side-grp{font-size:.52rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--txt3);margin:.6rem 0 .3rem}
    .lt-nav{display:flex;align-items:center;gap:.4rem;padding:.28rem .45rem;border-radius:5px;font-size:.6rem;color:var(--txt3);margin-bottom:.1rem;cursor:default}
    .lt-nav i{font-size:.7rem}
    .lt-nav.active{background:var(--blue-l);color:var(--blue);font-weight:600}
    .lt-main{padding:.85rem}
    .lt-title{font-size:.68rem;font-weight:700;color:var(--txt);margin-bottom:.6rem}
    .lt-kpis{display:grid;grid-template-columns:repeat(3,1fr);gap:.4rem;margin-bottom:.6rem}
    .lt-kpi{background:var(--bg);border:1px solid var(--border);border-radius:7px;padding:.5rem .55rem}
    .lt-kpi-v{font-size:.88rem;font-weight:800;color:var(--txt)}
    .lt-kpi-l{font-size:.52rem;color:var(--txt3);margin-top:.1rem}
    .lt-kpi-g{font-size:.5rem;color:#16a34a;margin-top:.12rem}
    .lt-th{display:grid;grid-template-columns:2fr 1fr 1fr 1fr;padding:.25rem .35rem;font-size:.53rem;color:var(--txt3);font-weight:700;text-transform:uppercase;border-bottom:1px solid var(--border)}
    .lt-tr{display:grid;grid-template-columns:2fr 1fr 1fr 1fr;padding:.35rem .35rem;font-size:.59rem;color:var(--txt2);border-bottom:1px solid var(--border);align-items:center}
    .lt-tr:last-child{border-bottom:none}
    .lt-badge{display:inline-block;font-size:.52rem;padding:.1rem .4rem;border-radius:100px;background:#dcfce7;color:#16a34a;font-weight:600}
    .lt-badge.w{background:#fef9c3;color:#a16207}
    .perk-grid{display:grid;grid-template-columns:1fr 1fr;gap:.8rem;margin-top:1.75rem}
    @media(max-width:480px){.perk-grid{grid-template-columns:1fr}}
    .perk{background:var(--bg);border:1px solid var(--border);border-radius:9px;padding:1rem;transition:border-color .15s,transform .15s}
    .perk:hover{border-color:var(--blue-b);transform:translateY(-2px)}
    .perk i{font-size:1.15rem;color:var(--blue);display:block;margin-bottom:.45rem}
    .perk strong{font-size:.85rem;font-weight:700;display:block;margin-bottom:.22rem;color:var(--txt)}
    .perk span{font-size:.78rem;color:var(--txt2);line-height:1.5}

    /* DOWNLOAD */
    .dl-sec{background:var(--bg);border-top:1px solid var(--border);border-bottom:1px solid var(--border)}
    .dl-head{text-align:center;max-width:560px;margin:0 auto 3rem}
    .dl-grid{display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;max-width:780px;margin:0 auto}
    .dl-card{background:var(--surface);border:1.5px solid var(--border);border-radius:16px;padding:2rem 1.75rem;display:flex;flex-direction:column;align-items:flex-start;gap:1rem;transition:border-color .2s,box-shadow .2s,transform .2s}
    .dl-card:hover{border-color:var(--blue-b);box-shadow:var(--shadow-md);transform:translateY(-3px)}
    .dl-icon{width:52px;height:52px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:1.55rem}
    .dl-icon.blue{background:var(--blue-l);border:1px solid var(--blue-b);color:var(--blue)}
    .dl-icon.green{background:#dcfce7;border:1px solid #86efac;color:#16a34a}
    .dl-card-title{font-size:1.1rem;font-weight:800;color:var(--txt)}
    .dl-card-desc{font-size:.875rem;color:var(--txt2);line-height:1.65;margin-top:-.2rem}
    .dl-feats{list-style:none;display:flex;flex-direction:column;gap:.4rem;width:100%}
    .dl-feats li{font-size:.82rem;color:var(--txt2);display:flex;align-items:center;gap:.45rem}
    .dl-feats li i{color:var(--blue);font-size:.85rem;flex-shrink:0}
    .dl-meta{display:flex;flex-wrap:wrap;gap:.5rem;width:100%;margin-top:-.1rem}
    .dl-chip{display:inline-flex;align-items:center;gap:.35rem;padding:.34rem .62rem;border-radius:999px;background:#f8fafc;border:1px solid var(--border);font-size:.74rem;font-weight:600;color:var(--txt2)}
    .dl-chip i{font-size:.92rem;color:var(--blue)}
    .dl-btn{display:inline-flex;align-items:center;gap:.5rem;margin-top:.5rem;padding:.7rem 1.4rem;border-radius:var(--radius);font-size:.88rem;font-weight:700;text-decoration:none;transition:background .15s,box-shadow .15s,transform .1s}
    .dl-btn.blue{background:var(--blue);color:#fff}
    .dl-btn.blue:hover{background:var(--blue-d);box-shadow:0 4px 12px rgba(37,99,235,.3);transform:translateY(-1px)}
    .dl-btn.green{background:#16a34a;color:#fff}
    .dl-btn.green:hover{background:#15803d;box-shadow:0 4px 12px rgba(22,163,74,.3);transform:translateY(-1px)}
    .dl-btn.disabled{background:#cbd5e1 !important;color:#475569 !important;cursor:not-allowed;pointer-events:none;box-shadow:none;transform:none}
    .dl-chip.soon{background:#fff7ed;border-color:#fed7aa;color:#c2410c}
    .dl-chip.soon i{color:#ea580c}
    .dl-badge{font-size:.7rem;color:var(--txt3);margin-top:.25rem}
    @media(max-width:680px){.dl-grid{grid-template-columns:1fr}}

    /* CTA */
    .cta-sec{background:var(--blue)}
    .cta-inner{max-width:640px;margin:0 auto;text-align:center}
    .cta-inner .eyebrow{color:rgba(255,255,255,.7)}
    .cta-inner h2{color:#fff;font-size:clamp(1.7rem,3.2vw,2.35rem)}
    .cta-inner p{color:rgba(255,255,255,.8);font-size:1rem;line-height:1.74;margin:1.1rem auto 2.25rem}
    .cta-row{display:flex;gap:.75rem;justify-content:center;flex-wrap:wrap}
    .btn-white{display:inline-flex;align-items:center;gap:.45rem;padding:.72rem 1.5rem;background:#fff;color:var(--blue);font-weight:700;font-size:.9rem;border-radius:var(--radius);text-decoration:none;transition:background .15s,transform .1s;white-space:nowrap}
    .btn-white:hover{background:#f0f7ff;color:var(--blue);transform:translateY(-1px)}
    .btn-wa-light{display:inline-flex;align-items:center;gap:.45rem;padding:.72rem 1.5rem;background:rgba(255,255,255,.15);color:#fff;font-weight:600;font-size:.9rem;border-radius:var(--radius);text-decoration:none;border:1px solid rgba(255,255,255,.3);transition:background .15s;white-space:nowrap}
    .btn-wa-light:hover{background:rgba(255,255,255,.22);color:#fff}

    /* FOOTER */
    footer{background:#0f172a;padding:2.5rem 5%;border-top:1px solid #1e293b}
    .foot-inner{max-width:1060px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1.25rem}
    @media(max-width:560px){.foot-inner{flex-direction:column;align-items:flex-start;gap:.75rem}.foot-links{flex-wrap:wrap;gap:1rem}}
    .foot-logo img{height:44px}
    .foot-links{display:flex;gap:1.75rem}
    .foot-links a{font-size:.82rem;color:#64748b;text-decoration:none;transition:color .15s}
    .foot-links a:hover{color:#94a3b8}
    .foot-copy{font-size:.78rem;color:#475569}

    /* BACK TO TOP */
    #back-top{position:fixed;bottom:2rem;right:2rem;z-index:90;width:40px;height:40px;background:var(--surface);border:1px solid var(--border);border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--txt2);font-size:1.1rem;box-shadow:var(--shadow-md);cursor:pointer;opacity:0;pointer-events:none;transition:opacity .2s,transform .2s;text-decoration:none}
    #back-top.show{opacity:1;pointer-events:auto}
    #back-top:hover{transform:translateY(-2px);color:var(--blue)}

    /* STATS COUNTER */
    .stats-sec{background:linear-gradient(135deg,#0f172a 0%,#1e3a5f 100%);padding:3rem 5%}
    .stats-grid{max-width:1060px;margin:0 auto;display:grid;grid-template-columns:repeat(4,1fr);gap:2rem;text-align:center}
    @media(max-width:700px){.stats-grid{grid-template-columns:repeat(2,1fr);gap:1.5rem}}
    .stat-item{padding:1rem}
    .stat-num{font-size:2.4rem;font-weight:900;background:linear-gradient(135deg,#93c5fd,#60a5fa);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;line-height:1.1}
    .stat-lbl{font-size:.82rem;color:rgba(255,255,255,.6);margin-top:.4rem;font-weight:500}

    /* TESTIMONIALS */
    .testi-sec{background:var(--bg);padding:5rem 5%}
    .testi-head{max-width:1060px;margin:0 auto 2.5rem;text-align:center}
    .testi-head h2{font-size:clamp(1.5rem,3vw,2.1rem);margin-top:.5rem}
    .testi-grid{max-width:1060px;margin:0 auto;display:grid;grid-template-columns:repeat(3,1fr);gap:1.5rem}
    @media(max-width:800px){.testi-grid{grid-template-columns:1fr}}
    .testi-card{background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:1.5rem;display:flex;flex-direction:column;gap:1rem;transition:transform .2s,box-shadow .2s}
    .testi-card:hover{transform:translateY(-4px);box-shadow:var(--shadow-lg)}
    .testi-stars{color:#f59e0b;font-size:1rem;letter-spacing:.1em}
    .testi-text{font-size:.9rem;color:var(--txt2);line-height:1.65;flex:1;font-style:italic}
    .testi-author{display:flex;align-items:center;gap:.75rem;padding-top:.75rem;border-top:1px solid var(--border)}
    .testi-avatar{width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,var(--blue),#60a5fa);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.85rem;flex-shrink:0}
    .testi-name{font-size:.875rem;font-weight:700;color:var(--txt)}
    .testi-loc{font-size:.75rem;color:var(--txt3);display:flex;align-items:center;gap:.2rem;margin-top:.1rem}

    /* REVEAL */
    .reveal{opacity:0;transform:translateY(20px);transition:opacity .55s ease,transform .55s ease}
    .reveal.show{opacity:1;transform:translateY(0)}
    .d1{transition-delay:.1s}.d2{transition-delay:.18s}.d3{transition-delay:.28s}
  </style>
</head>
<body>

<div id="scroll-prog"></div>

{{-- NAV --}}
<nav id="main-nav">
  <a href="{{ route('landing') }}" class="nav-logo" aria-label="Netking">
    <span class="nav-logo-text">Netking</span>
  </a>
  <div class="nav-links" id="nav-links">
    <a href="{{ route('landing') }}">Beranda</a>
    <a href="#cakupan">Cakupan Area</a>
    <a href="#harga">Pilihan Paket</a>
    <a href="#layanan">Keunggulan</a>
    <a href="#download">Aplikasi Netking</a>
  </div>
  <button class="nav-burger" id="nav-burger" aria-label="Menu">
    <span></span><span></span><span></span>
  </button>
</nav>

{{-- HERO --}}
<section class="hero" aria-label="Halaman utama">
  <div class="hero-blob"></div>
  <div class="reveal">
    <div class="hero-tag">
      <div class="hero-tag-dot"></div>
      🟢 Jaringan Kabel Netking · Terpantau Aktif 24/7
    </div>
    <h1>Pasang WiFi di Rumah Cepat &amp; Stabil.<br><em>100% Unlimited Tanpa FUP.</em></h1>
    <p class="hero-desc">
      Netking adalah ISP lokal yang mengoperasikan infrastruktur jaringan fiber optik secara mandiri. Koneksi dedicated ke setiap pelanggan, tanpa batasan kecepatan, didukung tim teknis dengan komitmen penanganan gangguan kurang dari 4 jam. Tersedia mulai dari <strong>Rp 100.000 per bulan</strong>.
    </p>
    <div class="hero-cta">
      <a href="#harga" class="btn-hero-primary"><i class='bx bx-package'></i> Pilihan Paket</a>
    </div>
    <div class="hero-trust">
      <div class="hero-trust-item"><i class='bx bx-check-circle'></i>Harga Flat</div>
      <div class="hero-trust-item"><i class='bx bx-check-circle'></i>Respons &lt; 4 Jam</div>
      <div class="hero-trust-item"><i class='bx bx-check-circle'></i>Monitoring 24/7</div>
      {{-- OLT Live Badge --}}
      <div class="olt-trust" id="olt-badge">
        <div class="olt-badge-dot"></div>
        <span id="olt-online">—</span>
        <span>OLT Online</span>
      </div>
    </div>
  </div>

  <div class="hero-visual">
    <div class="phone-wrap phone-float">
      <div class="phone-outer">
        <div class="phone-screen" style="min-height:490px;position:relative;">

          {{-- Screen A: Beranda --}}
          <div class="ip-screen" id="hp-beranda">
            <div class="ps-statusbar"><span class="ps-time">09:41</span><div class="ps-icons"><i class='bx bx-signal-5'></i><i class='bx bx-wifi'></i><i class='bx bx-battery'></i></div></div>
            <div class="ps-header">
              <div class="ps-header-greet">Selamat pagi,</div>
              <div class="ps-header-name">Ahmad Fauzi</div>
              <div class="ps-header-row">
                <div class="ps-online-pill"><div class="ps-online-dot"></div>Koneksi Aktif</div>
                <div class="ps-notif"><i class='bx bx-bell'></i></div>
              </div>
            </div>
            <div class="ps-bill-wrap"><div class="ps-bill">
              <div class="ps-bill-lbl">Tagihan Bulan Ini</div>
              <div class="ps-bill-amt">Rp 150.000</div>
              <div class="ps-bill-due">Jatuh tempo 10 April 2026</div>
              <div class="ps-bill-btn"><i class='bx bx-credit-card'></i> Bayar Sekarang</div>
            </div></div>
            <div class="ps-menu">
              <div class="ps-menu-item"><div class="ps-menu-icon"><i class='bx bx-receipt'></i></div><div class="ps-menu-lbl">Tagihan</div></div>
              <div class="ps-menu-item"><div class="ps-menu-icon"><i class='bx bx-wifi'></i></div><div class="ps-menu-lbl">Jaringan</div></div>
              <div class="ps-menu-item"><div class="ps-menu-icon"><i class='bx bx-support'></i></div><div class="ps-menu-lbl">Bantuan</div></div>
            </div>
            <div class="ps-rows">
              <div class="ps-rows-ttl">Ringkasan Akun</div>
              <div class="ps-row"><span class="ps-row-k">Paket Aktif</span><span class="ps-row-v">10 Mbps</span></div>
              <div class="ps-row"><span class="ps-row-k">Status</span><span class="ps-row-v ok">Aktif</span></div>
              <div class="ps-row"><span class="ps-row-k">Jatuh Tempo</span><span class="ps-row-v">10 April</span></div>
            </div>
          </div>

          {{-- Screen B: Tagihan --}}
          <div class="ip-screen" id="hp-tagihan" style="display:none;">
            <div class="ps-statusbar"><span class="ps-time">09:41</span><div class="ps-icons"><i class='bx bx-signal-5'></i><i class='bx bx-wifi'></i><i class='bx bx-battery'></i></div></div>
            <div class="ps-header" style="padding:.6rem 1rem .8rem;"><div class="ps-header-greet">Netking Customer</div><div class="ps-header-name">Histori Tagihan</div></div>
            <div style="padding:.65rem .85rem;background:#f1f5f9;">
              <div style="font-size:.56rem;color:var(--txt3);font-weight:700;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.5rem;">2026</div>
              <div style="background:#fff;border-radius:12px;padding:.3rem .7rem;box-shadow:0 1px 4px rgba(15,23,42,.08);">
                @foreach([['April 2026','Rp 150.000','Belum Dibayar',false],['Maret 2026','Rp 150.000','Lunas',true],['Feb 2026','Rp 150.000','Lunas',true],['Jan 2026','Rp 150.000','Lunas',true],['Des 2025','Rp 125.000','Lunas',true]] as [$bln,$nom,$st,$ok])
                <div style="display:flex;align-items:center;justify-content:space-between;padding:.42rem 0;border-bottom:1px solid #f1f5f9;">
                  <div><div style="font-size:.67rem;font-weight:600;color:var(--txt);">{{ $bln }}</div><div style="font-size:.58rem;color:var(--txt3);">{{ $nom }}</div></div>
                  @if($ok)<span style="font-size:.55rem;padding:.1rem .42rem;border-radius:100px;background:#dcfce7;color:#16a34a;font-weight:700;">{{ $st }}</span>
                  @else<span style="font-size:.55rem;padding:.1rem .42rem;border-radius:100px;background:#fef9c3;color:#a16207;font-weight:700;">{{ $st }}</span>@endif
                </div>
                @endforeach
              </div>
              <div style="margin-top:.65rem;background:var(--blue);color:#fff;border-radius:10px;padding:.55rem;text-align:center;font-size:.68rem;font-weight:700;"><i class='bx bx-credit-card' style="vertical-align:middle;margin-right:.2rem;"></i> Bayar Tagihan Bulan Ini</div>
            </div>
          </div>

          {{-- Screen C: Jaringan --}}
          <div class="ip-screen" id="hp-jaringan" style="display:none;">
            <div class="ps-statusbar"><span class="ps-time">09:41</span><div class="ps-icons"><i class='bx bx-signal-5'></i><i class='bx bx-wifi'></i><i class='bx bx-battery'></i></div></div>
            <div class="ps-header" style="padding:.6rem 1rem .8rem;"><div class="ps-header-greet">Netking Customer</div><div class="ps-header-name">Status Jaringan</div></div>
            <div class="st-wrap">
              <div class="st-hdr"><span class="st-hdr-t">Perangkat Aktif</span><span class="st-online-pill"><span class="st-online-dot"></span>Online</span></div>
              <div class="st-cards">
                <div class="st-card"><div class="st-card-ico"><i class='bx bx-time-five'></i></div><div class="st-card-val">14 Hari</div><div class="st-card-lbl">Uptime</div></div>
                <div class="st-card"><div class="st-card-ico"><i class='bx bx-devices'></i></div><div class="st-card-val">3</div><div class="st-card-lbl">Perangkat Aktif</div></div>
              </div>
              <div class="st-bar-wrap"><div class="st-bar-hdr"><span class="st-bar-lbl">Kualitas Sinyal (RX)</span><span class="st-bar-val">Bagus</span></div><div class="st-bar-bg"><div class="st-bar-fill" style="width:80%"></div></div></div>
              <div class="st-bar-wrap"><div class="st-bar-hdr"><span class="st-bar-lbl">Load Jaringan</span><span class="st-bar-val" style="color:#f59e0b">Sedang</span></div><div class="st-bar-bg"><div class="st-bar-fill" style="width:52%;background:linear-gradient(90deg,#f59e0b,#fcd34d)"></div></div></div>
              <div class="ps-row" style="margin-top:.3rem"><span class="ps-row-k">Paket</span><span class="ps-row-v">10 Mbps</span></div>
            </div>
          </div>

          {{-- Screen D: Profil --}}
          <div class="ip-screen" id="hp-profil" style="display:none;">
            <div class="ps-statusbar"><span class="ps-time">09:41</span><div class="ps-icons"><i class='bx bx-signal-5'></i><i class='bx bx-wifi'></i><i class='bx bx-battery'></i></div></div>
            <div class="ps-header" style="padding:.6rem 1rem .8rem;"><div class="ps-header-greet">Netking Customer</div><div class="ps-header-name">Profil Saya</div></div>
            <div style="padding:.75rem .85rem;">
              <div style="background:#fff;border-radius:14px;padding:.85rem;box-shadow:0 1px 4px rgba(15,23,42,.08);margin-bottom:.5rem;display:flex;align-items:center;gap:.7rem;">
                <div style="width:36px;height:36px;border-radius:50%;background:var(--blue-l);border:2px solid var(--blue-b);display:flex;align-items:center;justify-content:center;color:var(--blue);font-size:1.1rem;flex-shrink:0;"><i class='bx bx-user'></i></div>
                <div><div style="font-size:.78rem;font-weight:800;color:var(--txt);">Ahmad Fauzi</div><div style="font-size:.58rem;color:var(--txt3);">Pelanggan Aktif · ID #10482</div></div>
              </div>
              <div class="ps-rows" style="margin:0 0 .5rem;">
                <div class="ps-rows-ttl">Info Langganan</div>
                <div class="ps-row"><span class="ps-row-k">Paket</span><span class="ps-row-v">10 Mbps</span></div>
                <div class="ps-row"><span class="ps-row-k">Tagihan</span><span class="ps-row-v">Rp 150.000/bln</span></div>
                <div class="ps-row"><span class="ps-row-k">Bergabung</span><span class="ps-row-v">Jan 2025</span></div>
                <div class="ps-row"><span class="ps-row-k">Status</span><span class="ps-row-v ok">Aktif</span></div>
              </div>
              <div style="background:var(--blue);color:#fff;border-radius:10px;padding:.5rem;text-align:center;font-size:.65rem;font-weight:700;"><i class='bx bx-headphone' style="vertical-align:middle;margin-right:.2rem;"></i> Hubungi Dukungan</div>
            </div>
          </div>

          {{-- Bottom Nav Interaktif --}}
          <div class="ps-bottomnav" style="position:absolute;bottom:0;left:0;right:0;background:#fff;z-index:10;">
            <div class="ps-bn-item active" onclick="hpSwitch('beranda',this)" style="cursor:pointer"><i class='bx bxs-home'></i><span>Beranda</span><div class="ps-bn-dot"></div></div>
            <div class="ps-bn-item" onclick="hpSwitch('tagihan',this)" style="cursor:pointer"><i class='bx bxs-receipt'></i><span>Tagihan</span></div>
            <div class="ps-bn-item" onclick="hpSwitch('jaringan',this)" style="cursor:pointer"><i class='bx bx-wifi'></i><span>Jaringan</span></div>
            <div class="ps-bn-item" onclick="hpSwitch('profil',this)" style="cursor:pointer"><i class='bx bx-user'></i><span>Profil</span></div>
          </div>

        </div>{{-- /phone-screen --}}
      </div>
    </div>
    <div style="font-size:.72rem;color:rgba(255,255,255,.55);margin-top:.65rem;display:flex;align-items:center;gap:.3rem;"><i class='bx bx-tap' style="color:#93c5fd"></i> Ketuk nav untuk coba langsung</div>

  </div>
</section>

{{-- COVERAGE AREA --}}
<section class="coverage-sec" id="cakupan">
  <div class="coverage-wrap">
    <div class="reveal">
      <div class="coverage-top">
        <div>
      <div class="eyebrow">Jangkauan Layanan Netking</div>
      <h2>Jangkauan Area Layanan Netking</h2>
          <div class="coverage-helper">Pilih area untuk melihat status layanan.</div>
        </div>
        <span class="coverage-pro"><i class='bx bx-git-branch'></i>Ekspansi area dilakukan bertahap sesuai kesiapan jaringan</span>
      </div>
      <div class="cov-accordion" id="cov-accordion">
        <div class="cov-item open">
          <button type="button" class="cov-trigger">
            <div class="cov-left">
              <span class="cov-accent"></span>
              <span class="cov-name">Karawang</span>
            </div>
            <div class="cov-meta">
              <span class="cov-chip"><i class='bx bx-check-circle'></i>9 area aktif</span>
              <i class='bx bx-chevron-down cov-chevron'></i>
            </div>
          </button>
          <div class="cov-panel">
            <div class="cov-panel-inner">
              <div class="cov-content">
                <div class="cov-grid">
                  <span class="cov-tag"><i class='bx bx-map'></i>Teluk Bango</span>
                  <span class="cov-tag"><i class='bx bx-map'></i>Teluk Ambulu</span>
                  <span class="cov-tag"><i class='bx bx-map'></i>Rengasdengklok Utara</span>
                  <span class="cov-tag"><i class='bx bx-map'></i>Rengasdengklok Selatan</span>
                  <span class="cov-tag"><i class='bx bx-map'></i>Blok Kraton</span>
                  <span class="cov-tag"><i class='bx bx-map'></i>Jamblang</span>
                  <span class="cov-tag"><i class='bx bx-map'></i>Bakan Lio</span>
                  <span class="cov-tag"><i class='bx bx-map'></i>Bakan Tengah</span>
                  <span class="cov-tag"><i class='bx bx-map'></i>Perum Kalangsuria</span>
                </div>
                <div class="cov-note">Belum menemukan area Anda? Cek ketersediaan langsung via WhatsApp.</div>
                <div class="cov-cta">
                  <a
                    href="https://wa.me/6282249808698?text=Halo%20Netking,%20saya%20mau%20cek%20ketersediaan%20area%20internet%20rumah."
                    target="_blank"
                    rel="noopener noreferrer"
                    class="btn-wa-light"
                  >
                    <i class='bx bxl-whatsapp'></i> Cek Ketersediaan Karawang
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="cov-item">
          <button type="button" class="cov-trigger">
            <div class="cov-left">
              <span class="cov-accent"></span>
              <span class="cov-name">Tasikmalaya</span>
            </div>
            <div class="cov-meta">
              <span class="cov-chip soon"><i class='bx bx-time-five'></i>Coming Soon</span>
              <i class='bx bx-chevron-down cov-chevron'></i>
            </div>
          </button>
          <div class="cov-panel">
            <div class="cov-panel-inner">
              <div class="cov-content">
                <div class="cov-note">Coverage Tasikmalaya akan dibuka setelah kesiapan jaringan area selesai.</div>
              </div>
            </div>
          </div>
        </div>
        <div class="cov-item">
          <button type="button" class="cov-trigger">
            <div class="cov-left">
              <span class="cov-accent"></span>
              <span class="cov-name">Bandung</span>
            </div>
            <div class="cov-meta">
              <span class="cov-chip soon"><i class='bx bx-time-five'></i>Coming Soon</span>
              <i class='bx bx-chevron-down cov-chevron'></i>
            </div>
          </button>
          <div class="cov-panel">
            <div class="cov-panel-inner">
              <div class="cov-content">
                <div class="cov-note">Coverage Bandung akan dibuka setelah kesiapan backbone area selesai.</div>
              </div>
            </div>
          </div>
        </div>
        <div class="cov-item">
          <button type="button" class="cov-trigger">
            <div class="cov-left">
              <span class="cov-accent"></span>
              <span class="cov-name">Cikalong Wetan</span>
            </div>
            <div class="cov-meta">
              <span class="cov-chip soon"><i class='bx bx-time-five'></i>Coming Soon</span>
              <i class='bx bx-chevron-down cov-chevron'></i>
            </div>
          </button>
          <div class="cov-panel">
            <div class="cov-panel-inner">
              <div class="cov-content">
                <div class="cov-note">Coverage Cikalong Wetan sedang disiapkan untuk tahap aktivasi berikutnya.</div>
              </div>
            </div>
          </div>
        </div>
        <div class="cov-item">
          <button type="button" class="cov-trigger">
            <div class="cov-left">
              <span class="cov-accent"></span>
              <span class="cov-name">Garut</span>
            </div>
            <div class="cov-meta">
              <span class="cov-chip soon"><i class='bx bx-time-five'></i>Coming Soon</span>
              <i class='bx bx-chevron-down cov-chevron'></i>
            </div>
          </button>
          <div class="cov-panel">
            <div class="cov-panel-inner">
              <div class="cov-content">
                <div class="cov-note">Coverage Garut akan diprioritaskan pada gelombang ekspansi selanjutnya.</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- STATS COUNTER --}}
<section class="stats-sec">
  <div class="stats-grid">
    <div class="stat-item reveal">
      <div class="stat-num" data-target="500" data-suffix="+">500+</div>
      <div class="stat-lbl">Pelanggan Aktif</div>
    </div>
    <div class="stat-item reveal d1">
      <div class="stat-num" data-target="99.8" data-suffix="%" data-decimal="1">99.8%</div>
      <div class="stat-lbl">Uptime Jaringan</div>
    </div>
    <div class="stat-item reveal d2">
      <div class="stat-num" data-target="4" data-prefix="&lt;" data-suffix=" Jam">&lt;4 Jam</div>
      <div class="stat-lbl">Respons Gangguan</div>
    </div>
    <div class="stat-item reveal d3">
      <div class="stat-num" data-target="3" data-suffix="+ Tahun">3+ Tahun</div>
      <div class="stat-lbl">Beroperasi</div>
    </div>
  </div>
</section>

{{-- WHY --}}
<section class="why-sec" id="layanan">
  <div class="split">
    <div class="reveal">
      <div class="eyebrow">Kenapa Pilih Netking?</div>
      <h2>Standar Layanan yang Konsisten.</h2>
      <p class="body-txt">
        Koneksi stabil, respons cepat, dan harga flat — tiga hal yang paling sering diabaikan penyedia internet lain, dan tiga hal yang menjadi fondasi layanan Netking. Tim teknis kami aktif memantau jaringan 24 jam, merespons gangguan dalam hitungan jam, dan memastikan tagihan selalu sesuai yang telah disepakati.
      </p>
      <ul class="check-list">
        <li><i class='bx bx-check-circle'></i><span><strong>Jaringan Kabel Langsung ke Rumah:</strong> Koneksi dari titik distribusi kami langsung ke perangkat Anda — tidak bercampur atau berbagi secara sembarangan dengan pengguna lain.</span></li>
        <li><i class='bx bx-check-circle'></i><span><strong>Teknisi Lokal, Bukan Call Center:</strong> Anda lapor kendala, teknisi kami yang berangkat ke lokasi — target tiba dalam 4 jam, bukan esok hari.</span></li>
        <li><i class='bx bx-check-circle'></i><span><strong>Dipantau, Bukan Ditunggu:</strong> Jaringan kami dimonitor aktif 24 jam. Seringkali gangguan sudah kami tangani sebelum Anda sempat lapor.</span></li>
        <li><i class='bx bx-check-circle'></i><span><strong>Harga Flat, Tagihan Jelas:</strong> Tidak ada biaya tersembunyi. Pengingat jatuh tempo otomatis lewat aplikasi agar Anda tidak kena denda telat bayar.</span></li>
      </ul>
    </div>
    <div class="kpi-grid reveal d2">
      <div class="kpi-card">
        <div class="kpi-num">100%</div>
        <div class="kpi-lbl">Mandiri — Jaringan milik sendiri, bukan dari pihak ketiga</div>
      </div>
      <div class="kpi-card">
        <div class="kpi-num">Siaga</div>
        <div class="kpi-lbl">24/7 — Tim kami pantau jaringan agar Anda tidur tenang</div>
      </div>
      <div class="kpi-card">
        <div class="kpi-num">&lt; 4 Jam</div>
        <div class="kpi-lbl">Jaminan waktu maksimal teknisi tiba di rumah Anda</div>
      </div>
      <div class="kpi-card">
        <div class="kpi-num">App</div>
        <div class="kpi-lbl">Bayar tagihan &amp; cek koneksi cukup dari layar HP</div>
      </div>
    </div>
  </div>
</section>

{{-- PRICING --}}
<section class="pricing-sec" id="harga">
  <div class="container">
    <div class="pricing-head reveal">
      <div class="eyebrow">Harga Transparan, Tanpa Jebakan</div>
      <h2>Pilih Kecepatanmu.<br>Bayar Sesuai Angka yang Kamu Lihat.</h2>
      <p class="body-txt">Tidak ada biaya sewa alat yang disembunyikan. Tidak ada promo jebakan yang harganya naik setelah 3 bulan. Pilih paket yang pas dengan perangkat di rumahmu.</p>
    </div>
    <div class="pricing-grid">
      <div class="price-card reveal d1">
        <div class="price-speed">6</div>
        <div class="price-unit">Mbps — Paket Hemat</div>
        <div class="price-divider"></div>
        <div class="price-amount">Rp 100.000</div>
        <div class="price-period">per bulan</div>
        <div style="font-size:.75rem;color:var(--txt3);margin-top:.4rem;">Cocok untuk 1–3 perangkat</div>
        <ul class="price-feats">
          <li><i class='bx bx-check'></i>Upload &amp; download stabil</li>
          <li><i class='bx bx-check'></i>Jaringan kabel langsung ke rumah</li>
          <li><i class='bx bx-check'></i>Notifikasi tagihan via aplikasi</li>
          <li><i class='bx bx-check'></i>Akses penuh aplikasi pelanggan Netking</li>
        </ul>
      </div>
      <div class="price-card popular reveal d2">
        <div class="price-terlaris-badge">Terlaris 🔥</div>
        <div class="price-speed">8</div>
        <div class="price-unit">Mbps — Paket Keluarga</div>
        <div class="price-divider"></div>
        <div class="price-amount">Rp 125.000</div>
        <div class="price-period">per bulan</div>
        <div style="font-size:.75rem;color:var(--txt3);margin-top:.4rem;">Cocok untuk 3–5 perangkat</div>
        <ul class="price-feats">
          <li><i class='bx bx-check'></i>Upload &amp; download stabil</li>
          <li><i class='bx bx-check'></i>Jaringan kabel langsung ke rumah</li>
          <li><i class='bx bx-check'></i>Notifikasi tagihan via aplikasi</li>
          <li><i class='bx bx-check'></i>Akses penuh aplikasi pelanggan Netking</li>
          <li><i class='bx bx-check'></i>Prioritas penanganan gangguan</li>
        </ul>
      </div>
      <div class="price-card reveal d3">
        <div class="price-speed">10</div>
        <div class="price-unit">Mbps — Paket Super</div>
        <div class="price-divider"></div>
        <div class="price-amount">Rp 150.000</div>
        <div class="price-period">per bulan</div>
        <div style="font-size:.75rem;color:var(--txt3);margin-top:.4rem;">Cocok untuk 5+ perangkat</div>
        <ul class="price-feats">
          <li><i class='bx bx-check'></i>Upload &amp; download stabil</li>
          <li><i class='bx bx-check'></i>Jaringan kabel langsung ke rumah</li>
          <li><i class='bx bx-check'></i>Notifikasi tagihan via aplikasi</li>
          <li><i class='bx bx-check'></i>Akses penuh aplikasi pelanggan Netking</li>
          <li><i class='bx bx-check'></i>Prioritas utama penanganan gangguan</li>
        </ul>
      </div>
    </div>
  </div>
</section>

{{-- CUSTOMER APP --}}
<section id="pelanggan" style="background:var(--bg);">
  <div class="container">
    <div class="reveal" style="max-width:660px;">
      <div class="eyebrow">Kendali Penuh di Tanganmu</div>
      <h2>Urusan Internet Selesai<br>Tanpa Perlu Telepon CS.</h2>
      <p class="body-txt">
        Cek kecepatan WiFi, bayar tagihan bulanan, sampai melapor kendala—lakukan semuanya cukup dari HP Anda. Tidak perlu membuang waktu dan pulsa, semua fungsi kendali ada di saku Anda.
      </p>
      <ul class="feat-list">
        <li>
          <div class="feat-icon"><i class='bx bx-receipt'></i></div>
          <div class="feat-t">
            <strong>Pembayaran Sekali Klik</strong>
            <span>Lihat rincian, unduh invoice, dan bayar online secara instan.</span>
          </div>
        </li>
        <li>
          <div class="feat-icon"><i class='bx bx-wifi'></i></div>
          <div class="feat-t">
            <strong>Live Radar Koneksi</strong>
            <span>Pantau status sinyal, kondisi perangkat, dan waktu aktif jaringan secara real-time.</span>
          </div>
        </li>
        <li>
          <div class="feat-icon"><i class='bx bx-support'></i></div>
          <div class="feat-t">
            <strong>Pusat Bantuan Cepat</strong>
            <span>Akses panduan gangguan dan jalur bantuan partner/admin langsung dari aplikasi.</span>
          </div>
        </li>
      </ul>
      <div style="margin-top:1.75rem;">
        <a href="#download" class="btn-blue"><i class='bx bxl-android'></i> Kenali Fitur Aplikasi</a>
      </div>
    </div>
  </div>
</section>

{{-- TESTIMONIALS --}}
<section class="testi-sec">
  <div class="testi-head reveal">
    <div class="eyebrow">Dari Mulut Pelanggan Sendiri</div>
    <h2>Yang Mereka Rasakan,<br>Bukan Janji Kami.</h2>
  </div>
  <div class="testi-grid">
    <div class="testi-card reveal d1">
      <div class="testi-stars">★★★★★</div>
      <p class="testi-text">"Sudah 1 tahun pakai Netking, belum pernah mati mendadak. Kerja dari rumah tiap hari, meeting Zoom lancar terus. Ini yang saya cari dari dulu."</p>
      <div class="testi-author">
        <div class="testi-avatar">AR</div>
        <div>
          <div class="testi-name">Ahmad Rifqi</div>
          <div class="testi-loc"><i class='bx bx-map-pin'></i> Perumahan Griya Asri</div>
        </div>
      </div>
    </div>
    <div class="testi-card reveal d2">
      <div class="testi-stars">★★★★★</div>
      <p class="testi-text">"Teknisinya datang dalam 2 jam setelah telepon. Kabel putus kena hujan deras, langsung beres hari itu juga. Responsnya beda dari provider lain."</p>
      <div class="testi-author">
        <div class="testi-avatar">SW</div>
        <div>
          <div class="testi-name">Siti Wahyuni</div>
          <div class="testi-loc"><i class='bx bx-map-pin'></i> Jl. Merdeka No. 14</div>
        </div>
      </div>
    </div>
    <div class="testi-card reveal d3">
      <div class="testi-stars">★★★★★</div>
      <p class="testi-text">"Harganya gak naik-naik dari awal langganan. Tagihan selalu sesuai, ada notifikasi jatuh tempo juga. Ini yang paling penting."</p>
      <div class="testi-author">
        <div class="testi-avatar">BH</div>
        <div>
          <div class="testi-name">Budi Hartono</div>
          <div class="testi-loc"><i class='bx bx-map-pin'></i> RT 05, Blok C</div>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- DOWNLOAD APK --}}
<section class="dl-sec" id="download">
  <div class="container">
    <div class="dl-head reveal">
      <div class="eyebrow">100% Gratis Tanpa Iklan</div>
      <h2>Unduh Sekali.<br>Nikmati Kemudahannya Seterusnya.</h2>
      <p class="body-txt">Aplikasi customer Netking dirancang untuk memudahkan akses tagihan dan layanan akun Anda.</p>
    </div>
    <div class="dl-grid">
      <div class="dl-card reveal d1">
        <div class="dl-icon blue"><i class='bx bx-mobile-alt'></i></div>
        <div>
          <div class="dl-card-title">Netking Customer</div>
          <div class="dl-card-desc">Pusat kendali internet rumah Anda di OS Android.</div>
        </div>
        <ul class="dl-feats">
          <li><i class='bx bx-check-circle'></i>Cek tagihan &amp; pembayaran online</li>
          <li><i class='bx bx-check-circle'></i>Pantau status jaringan real-time</li>
          <li><i class='bx bx-check-circle'></i>Akses bantuan partner/admin dari aplikasi</li>
          <li><i class='bx bx-check-circle'></i>Pengingat jatuh tempo otomatis</li>
        </ul>
        <div class="dl-meta">
          <span class="dl-chip soon"><i class='bx bx-time-five'></i>Sementara belum tersedia</span>
        </div>
        <a href="javascript:void(0)" class="dl-btn blue disabled" aria-disabled="true" tabindex="-1"><i class='bx bx-time'></i> Belum tersedia</a>
      </div>
    </div>
  </div>
</section>

{{-- FOOTER --}}
<footer>
  <div class="foot-inner">
    <div class="foot-links">
      <a href="#layanan">Layanan</a>
      <a href="#harga">Pilihan Paket</a>
      <a href="#download">Aplikasi</a>
      <a href="#">Syarat &amp; Ketentuan</a>
      <a href="#">Kebijakan Privasi</a>
    </div>
    <div class="foot-copy">&copy; {{ date('Y') }} Netking. Semua hak dilindungi.</div>
  </div>
</footer>

{{-- BACK TO TOP --}}
<a href="#" id="back-top" aria-label="Kembali ke atas"><i class='bx bx-chevron-up'></i></a>

<script>
  // Scroll progress + nav shadow
  const prog=document.getElementById('scroll-prog'),nav=document.getElementById('main-nav'),backTop=document.getElementById('back-top');
  window.addEventListener('scroll',()=>{
    const pct=window.scrollY/(document.body.scrollHeight-window.innerHeight);
    prog.style.width=(pct*100)+'%';
    nav.classList.toggle('scrolled',window.scrollY>20);
    backTop.classList.toggle('show',window.scrollY>400);
  });

  // Reveal
  const io=new IntersectionObserver(e=>e.forEach(el=>{if(el.isIntersecting)el.target.classList.add('show');}),{threshold:.08});
  document.querySelectorAll('.reveal').forEach(el=>io.observe(el));
  setTimeout(()=>document.querySelectorAll('.hero .reveal').forEach(el=>el.classList.add('show')),150);

  // Active nav on scroll
  const sections=document.querySelectorAll('section[id]');
  const navLinks=document.querySelectorAll('.nav-links a');
  sections.forEach(s=>new IntersectionObserver(e=>e.forEach(el=>{
    if(el.isIntersecting) navLinks.forEach(a=>a.classList.toggle('active',a.getAttribute('href')==='#'+el.target.id));
  }),{rootMargin:'-40% 0px -55% 0px'}).observe(s));

  // Hamburger
  const burger=document.getElementById('nav-burger'),navLinksEl=document.getElementById('nav-links');
  burger.addEventListener('click',()=>{burger.classList.toggle('open');navLinksEl.classList.toggle('open');});
  navLinksEl.querySelectorAll('a').forEach(a=>a.addEventListener('click',()=>{burger.classList.remove('open');navLinksEl.classList.remove('open');}));

  // Back to top
  backTop.addEventListener('click',e=>{e.preventDefault();window.scrollTo({top:0,behavior:'smooth'});});

  // Coverage accordion interaction
  const covAcc = document.getElementById('cov-accordion');
  if (covAcc) {
    covAcc.querySelectorAll('.cov-trigger').forEach((btn) => {
      btn.addEventListener('click', () => {
        const item = btn.closest('.cov-item');
        const isOpen = item?.classList.contains('open');
        covAcc.querySelectorAll('.cov-item').forEach((node) => node.classList.remove('open'));
        if (!isOpen && item) item.classList.add('open');
      });
    });
  }

  // ── CountUp animation ──────────────────────────────────────────────────────
  function animateCount(el, target, duration=900) {
    if (!el || target === null || isNaN(target)) return;
    const start = performance.now();
    const from  = 0;
    el.style.animation = 'count-up .35s ease both';
    const tick = (now) => {
      const elapsed = now - start;
      const progress = Math.min(elapsed / duration, 1);
      // Ease out cubic
      const eased = 1 - Math.pow(1 - progress, 3);
      el.textContent = Math.round(from + (target - from) * eased);
      if (progress < 1) requestAnimationFrame(tick);
    };
    requestAnimationFrame(tick);
  }

  // ── Live OLT status fetch ──────────────────────────────────────────────────
  const $oltOnline = document.getElementById('olt-online');

  function fetchNetworkStatus() {
    fetch('/network-status')
      .then(r => r.json())
      .then(d => {
        animateCount($oltOnline, d.olt_online ?? d.olt_total);
      })
      .catch(() => {
        if ($oltOnline) $oltOnline.textContent = '—';
      });
  }

  // Fetch on load + every 60 seconds
  fetchNetworkStatus();
  setInterval(fetchNetworkStatus, 60000);

  // ── Hero Phone: Interactive Nav ──────────────────────────────────────
  function hpSwitch(screen, navEl) {
    document.querySelectorAll('.ip-screen[id^="hp-"]').forEach(s => s.style.display = 'none');
    const target = document.getElementById('hp-' + screen);
    if (target) target.style.display = 'block';
    document.querySelectorAll('[onclick*="hpSwitch"]').forEach(el => {
      el.classList.remove('active');
      const dot = el.querySelector('.ps-bn-dot');
      if (dot) dot.remove();
    });
    navEl.classList.add('active');
    const dot = document.createElement('div');
    dot.className = 'ps-bn-dot';
    navEl.appendChild(dot);
  }

  // ── Stats Counter Animation ─────────────────────────────────
  const statEls = document.querySelectorAll('.stat-num[data-target]');
  const counterObs = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting && !entry.target.dataset.animated) {
        entry.target.dataset.animated = 'true';
        const tgt     = parseFloat(entry.target.dataset.target);
        const suffix  = entry.target.dataset.suffix  || '';
        const prefix  = entry.target.dataset.prefix  || '';
        const decimal = parseInt(entry.target.dataset.decimal || '0');
        const dur = 1600, t0 = performance.now();
        const tick = (now) => {
          const p = Math.min((now - t0) / dur, 1);
          const eased = 1 - Math.pow(1 - p, 3);
          entry.target.textContent = prefix + (eased * tgt).toFixed(decimal) + suffix;
          if (p < 1) requestAnimationFrame(tick);
        };
        requestAnimationFrame(tick);
      }
    });
  }, { threshold: 0.5 });
  statEls.forEach(el => counterObs.observe(el));
</script>
</body>
</html>
