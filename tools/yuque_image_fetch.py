import hashlib
import io
import json
import mimetypes
from concurrent.futures import ThreadPoolExecutor, as_completed
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
HEADERS = {'User-Agent': UA, 'Referer': 'https://www.yuque.com/'}
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
    if ext == '.jpe':
        ext = '.jpg'
    if ext in {'.png', '.jpg', '.jpeg', '.webp', '.gif', '.bmp', '.tiff'}:
        return '.jpg' if ext == '.jpeg' else ext
    fm = (fmt or '').lower()
    return {'jpeg': '.jpg', 'png': '.png', 'webp': '.webp', 'gif': '.gif', 'bmp': '.bmp', 'tiff': '.tiff'}.get(fm, '.img')


def download(url):
    try:
        r = requests.get(url, headers=HEADERS, timeout=(8, 20))
        r.raise_for_status()
        data = r.content
        if len(data) < 2048:
            return url, None
        im = Image.open(io.BytesIO(data))
        w, h = im.size
        fmt = im.format
        if max(w, h) < 220 or min(w, h) < 90:
            return url, None
        sha = hashlib.sha256(data).hexdigest()
        ext = ext_for(r, fmt)
        out = STORE / f'{sha}{ext}'
        if not out.exists():
            out.write_bytes(data)
        return url, {'sha256': sha, 'file': str(out), 'width': w, 'height': h, 'bytes': len(data), 'url': url}
    except Exception as e:
        return url, {'error': repr(e), 'url': url}


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
    seen, urls = set(), []
    for root in roots:
        for img in root.find_all('img'):
            src = normalize_src(img)
            if src and src not in seen and looks_content_url(src):
                seen.add(src)
                urls.append(src)
    return urls


def main():
    page_map = []
    all_urls = set()
    for repo_dir in [p for p in ROOT.iterdir() if p.is_dir() and not p.name.startswith('_')]:
        for html_path in sorted(repo_dir.glob('*.html')):
            if html_path.name.startswith('_root_'):
                continue
            urls = page_candidates(html_path.read_text(encoding='utf-8', errors='ignore'))
            page_map.append((repo_dir.name, html_path, urls))
            all_urls.update(urls)

    print(f'UNIQUE_CANDIDATES {len(all_urls)} across {len(page_map)} pages', flush=True)
    cache = {}
    done = 0
    with ThreadPoolExecutor(max_workers=20) as ex:
        futs = {ex.submit(download, u): u for u in all_urls}
        for fut in as_completed(futs):
            url, item = fut.result()
            cache[url] = item
            done += 1
            if done % 50 == 0 or done == len(all_urls):
                kept = sum(1 for v in cache.values() if v and 'file' in v)
                print(f'DOWNLOAD_PROGRESS {done}/{len(all_urls)} kept={kept}', flush=True)

    total_refs = 0
    pages_with_images = 0
    for repo, html_path, urls in page_map:
        outdir = PAGES / repo
        outdir.mkdir(parents=True, exist_ok=True)
        items = [cache[u] for u in urls if cache.get(u) and 'file' in cache[u]]
        slug = html_path.name.split('__', 1)[0]
        meta = {
            'repo': repo,
            'page_html': str(html_path),
            'candidate_urls': len(urls),
            'kept_images': len(items),
            'images': items,
        }
        (outdir / f'{slug}.json').write_text(json.dumps(meta, ensure_ascii=False, indent=2), encoding='utf-8')
        if items:
            pages_with_images += 1
            total_refs += len(items)
        print(f'IMAGES {repo}/{slug}: candidates={len(urls)} kept={len(items)}', flush=True)

    store_files = list(STORE.iterdir())
    errors = [v for v in cache.values() if v and 'error' in v]
    manifest = {
        'unique_candidate_urls': len(all_urls),
        'unique_downloaded_images': len(store_files),
        'pages_with_images': pages_with_images,
        'page_image_references': total_refs,
        'store_bytes': sum(p.stat().st_size for p in store_files),
        'download_errors': len(errors),
    }
    (IMG_ROOT / 'manifest.json').write_text(json.dumps(manifest, ensure_ascii=False, indent=2), encoding='utf-8')
    print(json.dumps(manifest, ensure_ascii=False, indent=2), flush=True)

if __name__ == '__main__':
    main()
