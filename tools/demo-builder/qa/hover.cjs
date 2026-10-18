const { chromium } = require('playwright');
(async () => { const [,,url,sel,out]=process.argv; const b = await chromium.launch(); const p = await b.newPage({viewport:{width:1440,height:900}});
 await p.goto(url,{waitUntil:'load',timeout:90000}); await p.waitForTimeout(1500);
 const el = p.locator(sel).first(); await el.scrollIntoViewIfNeeded(); await p.waitForTimeout(1500);
 const box = await el.boundingBox(); const clip={x:Math.max(0,box.x-20),y:Math.max(0,box.y-20),width:Math.min(1400,box.width+40),height:box.height+40};
 await p.screenshot({path:out+'-a.png',clip}); await el.hover(); await p.waitForTimeout(1300); await p.screenshot({path:out+'-b.png',clip});
 console.log(await el.evaluate(e=>getComputedStyle(e).transform)); await b.close(); })();
