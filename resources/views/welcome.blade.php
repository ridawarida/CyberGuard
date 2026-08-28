<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CyberGuard | Digital Safety, Made Actionable</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <header class="home-nav">
        <a href="{{ route('home') }}" class="home-brand"><span class="brand-mark"><i class="fas fa-shield-halved"></i></span>CyberGuard</a>
        <nav class="home-links" aria-label="Main navigation">
            <a href="#how-it-works">How it works</a>
            <a href="#resources">Resources</a>
            <a href="{{ route('staff.login') }}" class="nav-staff"><i class="fas fa-lock me-2"></i>Staff login</a>
        </nav>
    </header>

    <main>
        <section class="home-hero">
            <div class="hero-photo"></div>
            <div class="hero-shade"></div>
            <div class="container hero-content">
                <p class="hero-kicker">Digital safety, made actionable</p>
                <h1>Turn a difficult moment into a protected record.</h1>
                <p class="hero-copy">CyberGuard helps people facing cyberbullying collect evidence safely, understand threatening language, and find a clear next step without creating an account.</p>
                <div class="hero-actions">
                    <a href="{{ route('incident.wizard.step1') }}" class="btn btn-primary-action"><i class="fas fa-file-circle-plus me-2"></i>Submit an incident</a>
                    <a href="{{ route('case-file.create') }}" class="btn btn-secondary-action"><i class="fas fa-folder-plus me-2"></i>Create a case file</a>
                </div>
                <div class="hero-note"><i class="fas fa-user-secret me-2"></i>Anonymous access. No account required.</div>
            </div>
            <div class="hero-scroll"><span></span>Explore support</div>
        </section>

        <section id="how-it-works" class="action-strip">
            <div class="container">
                <div class="section-intro"><p class="section-kicker">A more useful first step</p><h2>Support that moves with you.</h2><p>From the first report to a documented pattern, each tool is designed to give you control over what happens next.</p></div>
                <div class="row g-0 process-row">
                    <div class="col-md-4 process-item"><span class="process-number">01</span><i class="fas fa-file-shield"></i><h3>Report safely</h3><p>Submit what happened and keep your personal identity out of the report.</p><a href="{{ route('incident.wizard.step1') }}">Start a report <i class="fas fa-arrow-right"></i></a></div>
                    <div class="col-md-4 process-item"><span class="process-number">02</span><i class="fas fa-folder-tree"></i><h3>Build a case file</h3><p>Connect incidents that reveal an ongoing pattern of harassment or stalking.</p><a href="{{ route('case-file.create') }}">Create a case file <i class="fas fa-arrow-right"></i></a></div>
                    <div class="col-md-4 process-item"><span class="process-number">03</span><i class="fas fa-life-ring"></i><h3>Find support</h3><p>Find nearby crisis centers and hotlines based on your approximate city.</p><a href="{{ route('help-centers.index') }}">Find nearby help <i class="fas fa-arrow-right"></i></a></div>
                </div>
            </div>
        </section>

        <section id="resources" class="resources-band">
            <div class="container resources-grid">
                <div><p class="section-kicker">When you need a pause</p><h2>Tools for the space between reporting and recovery.</h2><p>CyberGuard combines practical evidence tools with immediate coping resources, so you are not left with a form and nowhere to go.</p></div>
                <div class="resource-links"><a href="{{ route('safe-space.index') }}"><span><i class="fas fa-wind"></i></span><strong>Digital safe space</strong><small>Reset, breathe, and regain focus.</small><i class="fas fa-arrow-up-right-from-square"></i></a><a href="{{ route('recovery-journal.index') }}"><span><i class="fas fa-seedling"></i></span><strong>Recovery journal</strong><small>Keep private notes on your progress.</small><i class="fas fa-arrow-up-right-from-square"></i></a><a href="{{ route('ticket.status.index') }}"><span><i class="fas fa-magnifying-glass"></i></span><strong>Track a submission</strong><small>Return to a report with its token.</small><i class="fas fa-arrow-up-right-from-square"></i></a></div>
            </div>
        </section>
    </main>

    <footer class="home-footer"><div class="container d-flex justify-content-between align-items-center flex-wrap gap-3"><span><i class="fas fa-shield-halved me-2"></i>CyberGuard</span><span>Private by design. Built for action.</span><a href="{{ route('staff.login') }}">Staff login <i class="fas fa-arrow-right ms-1"></i></a></div></footer>

    <style>
        :root{--ink:#172229;--cream:#f6f2eb;--crimson:#b51e3d;--teal:#1e5557;--line:#d9e1de}*{box-sizing:border-box}body{margin:0;font-family:'Cairo',sans-serif;color:var(--ink);background:var(--cream)}a{text-decoration:none}.home-nav{position:absolute;z-index:3;top:0;left:0;right:0;padding:24px clamp(20px,5vw,72px);display:flex;justify-content:space-between;align-items:center;color:#fff}.home-brand{color:#fff;font-size:24px;font-weight:800;display:flex;align-items:center;gap:10px}.brand-mark{width:38px;height:38px;display:grid;place-items:center;background:var(--crimson);font-size:17px}.home-links{display:flex;align-items:center;gap:28px}.home-links a{color:#fff;font-size:14px;font-weight:600}.home-links a:hover{color:#f3b7c2}.nav-staff{border:1px solid rgba(255,255,255,.5);padding:9px 15px}.home-hero{min-height:680px;position:relative;color:#fff;display:flex;align-items:center;overflow:hidden}.hero-photo{position:absolute;inset:0;background:url('https://images.unsplash.com/photo-1550751827-4bd374c3f58b?auto=format&fit=crop&w=2200&q=85') center/cover}.hero-shade{position:absolute;inset:0;background:linear-gradient(90deg,rgba(9,22,28,.9),rgba(9,22,28,.66) 46%,rgba(9,22,28,.2))}.hero-content{position:relative;z-index:1;padding-top:88px}.hero-kicker,.section-kicker{font-size:12px;letter-spacing:.16em;text-transform:uppercase;font-weight:800;color:#efb5c0;margin-bottom:16px}.hero-content h1{font-size:clamp(42px,6vw,82px);line-height:1.02;max-width:800px;font-weight:800;margin:0 0 22px}.hero-copy{max-width:610px;font-size:18px;line-height:1.7;color:#e0e9e8;margin-bottom:30px}.hero-actions{display:flex;gap:12px;flex-wrap:wrap}.btn{border-radius:0;font-weight:700;padding:13px 20px}.btn-primary-action{background:var(--crimson);color:#fff}.btn-primary-action:hover{background:#8f1730;color:#fff}.btn-secondary-action{border:1px solid rgba(255,255,255,.7);color:#fff;background:rgba(255,255,255,.08)}.btn-secondary-action:hover{background:#fff;color:var(--ink)}.hero-note{margin-top:24px;font-size:13px;color:#cfdddd}.hero-scroll{position:absolute;z-index:1;right:clamp(20px,5vw,72px);bottom:28px;font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:#dce8e7;display:flex;align-items:center;gap:10px}.hero-scroll span{height:34px;width:1px;background:var(--crimson)}.action-strip{background:var(--cream);padding:88px 0 76px}.section-intro{max-width:570px;margin-bottom:42px}.section-kicker{color:var(--crimson);margin-bottom:10px}.section-intro h2,.resources-grid h2{font-size:clamp(30px,4vw,48px);line-height:1.08;margin:0 0 14px;font-weight:800}.section-intro p,.resources-grid p{color:#667174;line-height:1.7;font-size:16px}.process-row{border-top:1px solid var(--line)}.process-item{padding:30px 28px 8px 0;border-right:1px solid var(--line);min-height:240px}.process-item:not(:first-child){padding-left:28px}.process-item:last-child{border-right:0}.process-number{color:var(--crimson);font-size:12px;font-weight:800;letter-spacing:.1em}.process-item>i{display:block;color:var(--teal);font-size:27px;margin:23px 0 15px}.process-item h3{font-size:22px;margin:0 0 8px}.process-item p{color:#667174;font-size:14px;line-height:1.6;max-width:280px}.process-item a{color:var(--crimson);font-weight:800;font-size:13px}.resources-band{background:var(--teal);color:#fff;padding:88px 0}.resources-grid{display:grid;grid-template-columns:1fr 1fr;gap:80px;align-items:center}.resources-grid h2{max-width:520px}.resources-grid p{color:#c7d9d8;max-width:540px}.resources-grid .section-kicker{color:#f0b4bf}.resource-links{border-top:1px solid rgba(255,255,255,.25)}.resource-links a{display:grid;grid-template-columns:48px 1fr 20px;grid-template-rows:auto auto;column-gap:16px;padding:20px 0;border-bottom:1px solid rgba(255,255,255,.25);color:#fff;align-items:center}.resource-links span{grid-row:1/3;width:42px;height:42px;background:#eeb1bb;color:var(--teal);display:grid;place-items:center}.resource-links strong{font-size:16px}.resource-links small{color:#c7d9d8;font-size:12px}.resource-links>a>i{grid-column:3;grid-row:1/3}.home-footer{background:#132d31;color:#b7c8c8;padding:22px 0;font-size:13px}.home-footer span:first-child{color:#fff;font-weight:800}.home-footer a{color:#efb5c0;font-weight:700}@media(max-width:700px){.home-nav{align-items:flex-start}.home-links{gap:10px;flex-direction:column;align-items:flex-end}.home-links>a:not(.nav-staff){display:none}.home-hero{min-height:680px}.hero-content{padding-top:120px}.hero-copy{font-size:16px}.process-item,.process-item:not(:first-child){border-right:0;border-bottom:1px solid var(--line);padding:26px 0;min-height:auto}.process-item:last-child{border-bottom:0}.resources-grid{grid-template-columns:1fr;gap:40px}.home-footer{font-size:12px}}
+    </style>
+</body>
+</html>
