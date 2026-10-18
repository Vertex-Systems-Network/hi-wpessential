const { chromium } = require('playwright');
(async () => {
  const [,, url, out, w] = process.argv;
  const b = await chromium.launch(); const p = await b.newPage({ viewport: { width: +(w||1440), height: 900 } });
  const errs=[]; p.on('pageerror', e=>errs.push(e.message));
  await p.goto(url, { waitUntil: 'load', timeout: 90000 }); await p.waitForTimeout(1500);
  await p.evaluate(async()=>{ for(let y=0;y<document.body.scrollHeight;y+=400){ window.scrollTo(0,y); await new Promise(r=>setTimeout(r,250)); } window.scrollTo(0,0); });
  await p.waitForTimeout(800);
  const sw = await p.evaluate(()=>document.documentElement.scrollWidth);
  await p.screenshot({ path: out, fullPage: true }); console.log('scrollWidth', sw, 'errors', errs.length, errs.slice(0,3).join(' | ')); await b.close();
})();
