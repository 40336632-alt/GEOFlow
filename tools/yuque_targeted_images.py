import asyncio, hashlib, io, json, mimetypes
from concurrent.futures import ThreadPoolExecutor, as_completed
from pathlib import Path
from urllib.parse import urlparse
import requests
from PIL import Image
from playwright.async_api import async_playwright

TARGETS={
 'psakeh/aevsxn':'https://www.yuque.com/oxixzd/psakeh/aevsxn',
 'psakeh/diew3x':'https://www.yuque.com/oxixzd/psakeh/diew3x',
 'psakeh/hp99qo':'https://www.yuque.com/oxixzd/psakeh/hp99qo',
 'psakeh/yyu6ca':'https://www.yuque.com/oxixzd/psakeh/yyu6ca',
 'oc6bsd/on49d2':'https://www.yuque.com/oxixzd/oc6bsd/on49d2',
 'uvt1iu/grm12t':'https://www.yuque.com/oxixzd/uvt1iu/grm12t',
}
OUT=Path('targeted_images'); STORE=OUT/'store'; PAGES=OUT/'pages'; STORE.mkdir(parents=True,exist_ok=True); PAGES.mkdir(parents=True,exist_ok=True)
HEADERS={'User-Agent':'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/127 Safari/537.36','Referer':'https://www.yuque.com/'}
EX=['avatar','emoji','logo','favicon','icon','w_16','w_24','w_32','w_40','w_48','w_56','w_64','w_72','w_80','w_96']

def good_url(u):
 lo=u.lower(); host=urlparse(u).netloc.lower(); return u.startswith('http') and not any(x in lo for x in EX) and (host.endswith('nlark.com') or host.endswith('alipayobjects.com') or host.endswith('alicdn.com'))

def download(u):
 try:
  r=requests.get(u,headers=HEADERS,timeout=(8,20)); r.raise_for_status(); data=r.content
  if len(data)<2048:return u,None
  im=Image.open(io.BytesIO(data)); w,h=im.size; fmt=(im.format or '').lower()
  if max(w,h)<220 or min(w,h)<90:return u,None
  sha=hashlib.sha256(data).hexdigest(); ct=(r.headers.get('content-type') or '').split(';')[0]; ext=mimetypes.guess_extension(ct) or {'jpeg':'.jpg','png':'.png','webp':'.webp','gif':'.gif'}.get(fmt,'.img')
  if ext=='.jpe':ext='.jpg'
  f=STORE/f'{sha}{ext}';
  if not f.exists():f.write_bytes(data)
  return u,{'sha256':sha,'file':str(f),'width':w,'height':h,'bytes':len(data),'url':u}
 except Exception as e:return u,{'error':repr(e),'url':u}

async def collect(page,url):
 resp=await page.goto(url,wait_until='domcontentloaded',timeout=30000); await page.wait_for_timeout(2200)
 # Force all lazy sections into view: window + every scrollable pane, several passes.
 for _ in range(16):
  await page.evaluate('''() => { window.scrollBy(0, Math.max(700, innerHeight*0.8)); for (const el of document.querySelectorAll('*')) { const s=getComputedStyle(el); if ((s.overflowY==='auto'||s.overflowY==='scroll') && el.scrollHeight>el.clientHeight) el.scrollTop=Math.min(el.scrollHeight,el.scrollTop+Math.max(700,el.clientHeight*0.9)); }}''')
  await page.wait_for_timeout(280)
 await page.evaluate('window.scrollTo(0,0)'); await page.wait_for_timeout(500)
 vals=await page.eval_on_selector_all('img','els=>els.map(i=>({src:i.src||i.getAttribute("data-src")||i.getAttribute("data-original")||"",w:i.naturalWidth||i.width,h:i.naturalHeight||i.height}))')
 urls=[]; seen=set()
 for x in vals:
  u=x.get('src','')
  if u and u not in seen and good_url(u): seen.add(u); urls.append(u)
 print('TARGET',url,'status',resp.status if resp else None,'candidates',len(urls),flush=True)
 return urls

async def main():
 mapping={}; allu=set()
 async with async_playwright() as p:
  b=await p.chromium.launch(headless=True); c=await b.new_context(viewport={'width':1440,'height':1200},locale='zh-CN'); page=await c.new_page()
  for key,url in TARGETS.items():
   try: urls=await collect(page,url)
   except Exception as e: print('ERR',key,repr(e),flush=True); urls=[]
   mapping[key]=urls; allu.update(urls)
  await b.close()
 cache={}
 with ThreadPoolExecutor(max_workers=20) as ex:
  fs=[ex.submit(download,u) for u in allu]
  for f in as_completed(fs): u,v=f.result(); cache[u]=v
 for key,urls in mapping.items():
  repo,slug=key.split('/'); d=PAGES/repo; d.mkdir(parents=True,exist_ok=True); imgs=[cache[u] for u in urls if cache.get(u) and 'file' in cache[u]]
  (d/f'{slug}.json').write_text(json.dumps({'repo':repo,'slug':slug,'candidate_urls':len(urls),'kept_images':len(imgs),'images':imgs},ensure_ascii=False,indent=2),encoding='utf-8'); print('KEPT',key,len(imgs),flush=True)
 files=list(STORE.iterdir()); mf={'unique_images':len(files),'bytes':sum(f.stat().st_size for f in files),'pages':{k:len([u for u in v if cache.get(u) and 'file' in cache[u]]) for k,v in mapping.items()}}
 (OUT/'manifest.json').write_text(json.dumps(mf,ensure_ascii=False,indent=2),encoding='utf-8'); print(json.dumps(mf,ensure_ascii=False),flush=True)

if __name__=='__main__':asyncio.run(main())
