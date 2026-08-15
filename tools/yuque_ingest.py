import asyncio
import json
import re
import shutil
from pathlib import Path
from urllib.parse import urljoin, urlparse

from playwright.async_api import async_playwright

ROOTS = [
    "https://www.yuque.com/oxixzd/psakeh",
    "https://www.yuque.com/oxixzd/oc6bsd",
    "https://www.yuque.com/oxixzd/uvt1iu",
]

OUT = Path("yuque_dump")
OUT.mkdir(exist_ok=True)


def clean_name(s: str) -> str:
    s = re.sub(r"[\\/:*?\"<>|]+", "_", s.strip())
    s = re.sub(r"\s+", " ", s)
    return (s[:120] or "untitled").strip(" .")


def slug_from_url(url: str) -> str:
    p = urlparse(url).path.strip("/").split("/")
    return p[-1] if p else "index"


def canonical_doc_url(href: str, repo_root: str):
    if not href:
        return None
    u = urljoin(repo_root + "/", href)
    parsed = urlparse(u)
    if parsed.netloc not in {"www.yuque.com", "yuque.com"}:
        return None
    root_path = urlparse(repo_root).path.rstrip("/")
    path = parsed.path.rstrip("/")
    if not path.startswith(root_path):
        return None
    # only root or one doc slug below the repo; discard settings/search/etc
    rel = path[len(root_path):].strip("/")
    if rel and "/" in rel:
        return None
    if rel in {"dashboard", "settings", "search"}:
        return None
    return f"https://www.yuque.com{path}"


async def dismiss_overlays(page):
    labels = ["我知道了", "知道了", "同意", "接受", "关闭", "取消", "稍后"]
    for label in labels:
        try:
            loc = page.get_by_text(label, exact=True)
            if await loc.count():
                await loc.first.click(timeout=800)
        except Exception:
            pass


async def discover_repo(page, repo_root: str):
    print(f"DISCOVER {repo_root}")
    await page.goto(repo_root, wait_until="domcontentloaded", timeout=90000)
    try:
        await page.wait_for_load_state("networkidle", timeout=25000)
    except Exception:
        pass
    await dismiss_overlays(page)
    await page.wait_for_timeout(3500)

    links = await page.eval_on_selector_all(
        "a[href]",
        "els => els.map(a => ({href:a.getAttribute('href'), text:(a.innerText||'').trim()}))",
    )
    found = {repo_root}
    labels = {}
    for item in links:
        u = canonical_doc_url(item.get("href"), repo_root)
        if u:
            found.add(u)
            if item.get("text"):
                labels[u] = item["text"]

    # Try extracting href-like strings from rendered HTML / embedded app data as a backup.
    html = await page.content()
    root_path = urlparse(repo_root).path.rstrip("/")
    pat = re.compile(re.escape(root_path) + r"/([A-Za-z0-9_-]{2,})")
    for slug in pat.findall(html):
        found.add(f"https://www.yuque.com{root_path}/{slug}")

    return sorted(found), labels, html


async def extract_page(page, url: str):
    print(f"READ {url}")
    resp = await page.goto(url, wait_until="domcontentloaded", timeout=90000)
    status = resp.status if resp else None
    try:
        await page.wait_for_load_state("networkidle", timeout=25000)
    except Exception:
        pass
    await dismiss_overlays(page)
    await page.wait_for_timeout(2200)

    title = (await page.title()).strip()
    html = await page.content()

    # Prefer Yuque article/editor containers, with progressively broader fallbacks.
    selectors = [
        ".lake-content",
        "[class*='lake-content']",
        "article",
        "main",
    ]
    text = ""
    used = None
    for sel in selectors:
        try:
            loc = page.locator(sel)
            n = await loc.count()
            if n:
                candidates = []
                for i in range(min(n, 8)):
                    t = (await loc.nth(i).inner_text()).strip()
                    if len(t) > 80:
                        candidates.append(t)
                if candidates:
                    text = max(candidates, key=len)
                    used = sel
                    break
        except Exception:
            pass
    if not text:
        text = (await page.locator("body").inner_text()).strip()
        used = "body"

    # collect outbound same-repo document links for BFS expansion
    links = await page.eval_on_selector_all(
        "a[href]", "els => els.map(a => a.getAttribute('href'))"
    )
    return {
        "url": url,
        "status": status,
        "title": title,
        "selector": used,
        "text": text,
        "html": html,
        "links": links,
    }


async def main():
    manifest = {"roots": ROOTS, "repos": []}
    async with async_playwright() as p:
        browser = await p.chromium.launch(headless=True)
        context = await browser.new_context(
            viewport={"width": 1440, "height": 1200},
            locale="zh-CN",
            user_agent=(
                "Mozilla/5.0 (Windows NT 10.0; Win64; x64) "
                "AppleWebKit/537.36 (KHTML, like Gecko) "
                "Chrome/127.0.0.0 Safari/537.36"
            ),
        )
        page = await context.new_page()

        for repo_root in ROOTS:
            repo_slug = urlparse(repo_root).path.strip("/").split("/")[-1]
            repo_dir = OUT / repo_slug
            repo_dir.mkdir(parents=True, exist_ok=True)

            initial, labels, root_html = await discover_repo(page, repo_root)
            (repo_dir / "_root_rendered.html").write_text(root_html, encoding="utf-8")

            queue = list(initial)
            seen = set()
            pages_meta = []

            while queue:
                url = queue.pop(0)
                if url in seen:
                    continue
                seen.add(url)
                try:
                    data = await extract_page(page, url)
                except Exception as e:
                    pages_meta.append({"url": url, "error": repr(e)})
                    continue

                # Expand links found on every page.
                for href in data.pop("links"):
                    u = canonical_doc_url(href, repo_root)
                    if u and u not in seen and u not in queue:
                        queue.append(u)

                slug = slug_from_url(url)
                title = data["title"] or labels.get(url) or slug
                base = clean_name(f"{slug}__{title}")
                txt_path = repo_dir / f"{base}.md"
                html_path = repo_dir / f"{base}.html"

                md = (
                    f"# {title}\n\n"
                    f"- Source: {url}\n"
                    f"- HTTP status: {data['status']}\n"
                    f"- Extracted from: `{data['selector']}`\n\n"
                    f"---\n\n{data['text']}\n"
                )
                txt_path.write_text(md, encoding="utf-8")
                html_path.write_text(data["html"], encoding="utf-8")

                pages_meta.append({
                    "url": url,
                    "status": data["status"],
                    "title": title,
                    "slug": slug,
                    "text_file": str(txt_path),
                    "html_file": str(html_path),
                    "chars": len(data["text"]),
                    "selector": data["selector"],
                })

            repo_manifest = {
                "root": repo_root,
                "repo_slug": repo_slug,
                "page_count": len(pages_meta),
                "pages": pages_meta,
            }
            (repo_dir / "manifest.json").write_text(
                json.dumps(repo_manifest, ensure_ascii=False, indent=2), encoding="utf-8"
            )
            manifest["repos"].append(repo_manifest)

        await browser.close()

    (OUT / "manifest.json").write_text(
        json.dumps(manifest, ensure_ascii=False, indent=2), encoding="utf-8"
    )
    shutil.make_archive("yuque_dump", "zip", OUT)
    print(json.dumps({
        "repo_counts": {r["repo_slug"]: r["page_count"] for r in manifest["repos"]},
        "zip": "yuque_dump.zip",
    }, ensure_ascii=False, indent=2))


if __name__ == "__main__":
    asyncio.run(main())
