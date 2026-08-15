import hashlib
import io
import json
import mimetypes
import re
from pathlib import Path
from urllib.parse import urlparse

import requests
from bs4 import BeautifulSoup
from PIL import Image

ROOT = Path('yuque_dump')
IMG_ROOT = ROOT / '_images'
STORE = IMG_ROOT / 'store'
PAGES = IMG_ROOT / 'pages'
STORE.mkdir(parents=True, exist_ok=True)
PAGES.mkdir(parents=True, exist_ok=True)

UA = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/127 Safari/537.36'
S = requests.Session()
S.headers.update({'User-Agent': UA, 'Referer': 'https://www.yuque.com/'})

EXCLUDE_PATTERNS = [
    'avatar', 'emoji', 'logo', 'favicon', 'icon',
    'w_16', 'w_24', 'w_32', 'w_40', 'w_48', 'w_56', 'w_64', 'w_72', 'w_80', 'w_96',
]


def normalize_src(img):
    for key in ('src', 'data-src', 'data-original', 'data-actualsrc'):
        v = img.get(key)
        if v and isinstance(v, str) and v.startswith('http'):
            return v
    srcset = img.get('srcset')
    if srcset:
        cand = srcset.split(',')[-1].strip().split(' ')[0]
        if cand.startswith('http'):
            return cand
    return None


def looks_content_url(url):
    low = url.lower()
    if any(p in low for p in EXCLUDE_PATTERNS):
        return False
    host = urlparse(url).netloc.lower()
    return host.endswith('nlark.com') or host.endswith('alipayobjects.com') or host.endswith('alicdn.com')


def ext_for(resp, fmt):
    ct = (resp.headers.get('content-type') or '').split(';')[0].strip().lower()
    ext = mimetypes.guess_extension(ct) if ct else None
    if ext == '.jpe': ext = '.jpg'
    if ext in {'.png','.jpg','.jpeg','.webp','.gif','.bmp','.tiff'}:
        return '.jpg' if ext == '.jpeg' else ext
    fm = (fmt or '').lower()
    return { 'jpeg':'.jpg', 'png':'.png', 'webp':'.webp', 'gif':'.gif', 'bmp':'.bmp', 'tiff':'.tiff' }.get(fm, '.img')


def download(url):
    r = S.get(url, timeout=25)
    r.raise_for_status()
    data = r.content
    if len(data) < 2048:
        return None
    try:
        im = Image.open(io.BytesIO(data))
        w, h = im.size
        fmt = im.format
    except Exception:
        return None
    # remove avatars, decorative dots and tiny UI; keep charts/screenshots and document figures
    if max(w, h) < 220 or min(w, h) < 90:
        return None
    sha = hashlib.sha256(data).hexdigest()
    ext = ext_for(r, fmt)
    out = STORE / f'{sha}{ext}'
    if not out.exists():
        out.write_bytes(data)
    return {'sha256': sha, 'file': str(out), 'width': w, 'height': h, 'bytes': len(data), 'url': url}


def page_candidates(html):
    soup = BeautifulSoup(html, 'html.parser')
    roots = []
    for sel in ['.lake-content', '[class*="lake-content"]', 'article', 'main']:
        try:
            roots.extend(soup.select(sel))
        except Exception:
            pass
    if not roots:
        roots = [soup.body or soup]
    seen = set(); urls = []
    for root in roots:
        for img in root.find_all('img'):
            src = normalize_src(img)
            if src and src not in seen and looks_content_url(src):
                seen.add(src); urls.append(src)
    return urls


def main():
    global_cache = {}
    total_refs = 0
    pages_with_images = 0
    for repo_dir in [p for p in ROOT.iterdir() if p.is_dir() and not p.name.startswith('_')]:
        outdir = PAGES / repo_dir.name
        outdir.mkdir(parents=True, exist_ok=True)
        for html_path in sorted(repo_dir.glob('*.html')):
            if html_path.name.startswith('_root_'):
                continue
            html = html_path.read_text(encoding='utf-8', errors='ignore')
            urls = page_candidates(html)
            items = []
            for url in urls:
                if url not in global_cache:
                    try:
                        global_cache[url] = download(url)
                    except Exception as e:
                        global_cache[url] = {'error': repr(e), 'url': url}
                item = global_cache[url]
                if item and 'file' in item:
                    items.append(item)
            slug = html_path.name.split('__', 1)[0]
            meta = {
                'repo': repo_dir.name,
                'page_html': str(html_path),
                'candidate_urls': len(urls),
                'kept_images': len(items),
                'images': items,
            }
            (outdir / f'{slug}.json').write_text(json.dumps(meta, ensure_ascii=False, indent=2), encoding='utf-8')
            if items:
                pages_with_images += 1
                total_refs += len(items)
            print(f'IMAGES {repo_dir.name}/{slug}: candidates={len(urls)} kept={len(items)}', flush=True)

    store_files = list(STORE.iterdir())
    manifest = {
        'unique_downloaded_images': len(store_files),
        'pages_with_images': pages_with_images,
        'page_image_references': total_refs,
        'store_bytes': sum(p.stat().st_size for p in store_files),
    }
    (IMG_ROOT / 'manifest.json').write_text(json.dumps(manifest, ensure_ascii=False, indent=2), encoding='utf-8')
    print(json.dumps(manifest, ensure_ascii=False, indent=2), flush=True)

if __name__ == '__main__':
    main()
