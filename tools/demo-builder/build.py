"""Build demo packages.

    python3 tools/demo-builder/build.py            # write content.json + demo.json for every demo
    python3 tools/demo-builder/build.py --zip      # also write dist/<id>-<builder>.zip for the server

Every demo is served by the WPEssential Server: upload dist/<id>-<builder>.zip to the demo
(WPEssential → Demos) and the server hands it out by plan (free demos to everyone, Pro demos
through a signed link after the license and plan are checked). Base also stays bundled in
core/demos/base as an offline fallback; Pro demo sources live in demos/<id>.
"""
import json
import os
import sys
import zipfile

HERE = os.path.dirname(os.path.abspath(__file__))
ROOT = os.path.dirname(os.path.dirname(HERE))
sys.path.insert(0, HERE)

import base_elementor  # noqa: E402
import maison  # noqa: E402
import pulse  # noqa: E402


def write(path, data):
    os.makedirs(os.path.dirname(path), exist_ok=True)
    with open(path, 'w', encoding='utf-8') as f:
        json.dump(data, f, ensure_ascii=False, indent='\t')
        f.write('\n')


def demo_json(mod):
    d = dict(mod.DEMO)
    plugins = d.pop('plugins')
    d['builders'] = {
        'gutenberg': {'package': 'gutenberg', 'plugins': plugins},
        'elementor': {'package': 'elementor', 'plugins': [
            {'slug': 'elementor', 'name': 'Elementor', 'source': 'wporg', 'state': 'required'}] + plugins},
    }
    return d


def zip_package(src_dir, builder, out):
    os.makedirs(os.path.dirname(out), exist_ok=True)
    with zipfile.ZipFile(out, 'w', zipfile.ZIP_DEFLATED) as z:
        z.write(os.path.join(src_dir, builder, 'content.json'), 'content.json')
        media = os.path.join(src_dir, 'media')
        for name in sorted(os.listdir(media)) if os.path.isdir(media) else []:
            z.write(os.path.join(media, name), 'media/' + name)


def main():
    for mod in (maison, pulse):
        base = os.path.join(ROOT, 'demos', mod.ID)
        write(os.path.join(base, 'demo.json'), demo_json(mod))
        for builder in ('gutenberg', 'elementor'):
            write(os.path.join(base, builder, 'content.json'), mod.package(builder))
            if '--zip' in sys.argv:
                zip_package(base, builder, os.path.join(ROOT, 'dist', '%s-%s.zip' % (mod.ID, builder)))
        print('built', mod.ID)

    # Base demo: Elementor package next to the existing Gutenberg one.
    base = os.path.join(ROOT, 'core', 'demos', 'base')
    write(os.path.join(base, 'elementor', 'content.json'), base_elementor.package())
    demo = json.load(open(os.path.join(base, 'demo.json')))
    demo['builders']['elementor'] = {'package': 'elementor', 'plugins': [
        {'slug': 'elementor', 'name': 'Elementor', 'source': 'wporg', 'state': 'required'},
        {'slug': 'woocommerce', 'name': 'WooCommerce', 'source': 'wporg', 'state': 'recommended'}]}
    with open(os.path.join(base, 'demo.json'), 'w', encoding='utf-8') as f:
        json.dump(demo, f, ensure_ascii=False, indent='\t')
        f.write('\n')
    print('built base elementor')
    if '--zip' in sys.argv:
        # Base images ship inside the theme (source "theme"), so its zips hold content.json only.
        for builder in ('gutenberg', 'elementor'):
            zip_package(base, builder, os.path.join(ROOT, 'dist', 'base-%s.zip' % builder))


if __name__ == '__main__':
    main()
