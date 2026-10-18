const { chromium } = require('playwright');
(async () => { const b = await chromium.launch(); const p = await b.newPage({ viewport:{width:1440,height:900}});
 await p.goto('' + (process.env.WP_URL || 'http://127.0.0.1:8899') + '/wp-login.php'); await p.fill('#user_login','admin'); await p.fill('#user_pass','admin123'); await p.click('#wp-submit'); await p.waitForURL(/wp-admin/);
 const ids = process.argv.slice(2);
 for (const id of ids) {
   await p.goto('' + (process.env.WP_URL || 'http://127.0.0.1:8899') + '/wp-admin/post.php?post='+id+'&action=edit', { waitUntil: 'domcontentloaded', timeout: 90000 });
   await p.waitForFunction(()=>window.wp && wp.data && wp.data.select('core/block-editor').getBlocks().length>0, null, {timeout:60000}).catch(()=>{});
   const r = await p.evaluate(()=>{ const bad=[]; const walk=bs=>bs.forEach(x=>{ if(!x.isValid) bad.push(x.name+': '+((x.validationIssues||[]).map(i=>i.args&&i.args.slice(0,3).join(' ')).join(' / ')).slice(0,300)); walk(x.innerBlocks); }); const all=wp.data.select('core/block-editor').getBlocks(); walk(all); return {n:all.length,bad}; });
   console.log(id, 'blocks', r.n, 'invalid', r.bad.length); r.bad.slice(0,4).forEach(x=>console.log('   ',x));
 } await b.close(); })();
