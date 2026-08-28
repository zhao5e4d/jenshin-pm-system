# -*- coding: utf-8 -*-
"""Generate valid UTF-8 guide SVGs. Run from this directory: python _generate.py"""
from __future__ import print_function
from xml.sax.saxutils import escape
import os

OUT = os.path.dirname(os.path.abspath(__file__))

def t(x, y, fill, size, text, weight=None, family=None):
    fw = ' font-weight="%s"' % weight if weight else ''
    fam = family or FONT
    return '<text x="%s" y="%s" fill="%s" font-family="%s" font-size="%s"%s>%s</text>' % (
        x, y, fill, fam, size, fw, escape(text)
    )

def check(cx, cy):
    return (
        '<circle cx="%s" cy="%s" r="7" fill="#6C5CE7"/>'
        '<path d="M%s %sl1.8 1.8 4-4" stroke="#fff" stroke-width="1.4" fill="none" stroke-linecap="round"/>'
        % (cx, cy, cx - 2.8, cy + 0.1)
    )

def dots(active):
    parts = []
    for i, x in enumerate((216, 232, 248, 264)):
        op = '' if i == active else ' fill-opacity=".35"'
        parts.append('<circle cx="%s" cy="444" r="4" fill="#FFFFFF"%s/>' % (x, op))
    return '\n  '.join(parts)

def wrap(inner):
    return (
        '<?xml version="1.0" encoding="UTF-8"?>\n'
        '<svg width="800" height="460" viewBox="0 0 800 460" fill="none" '
        'xmlns="http://www.w3.org/2000/svg">\n%s\n</svg>\n' % inner
    )

def write(name, body):
    path = os.path.join(OUT, name)
    with open(path, 'w', encoding='utf-8', newline='\n') as f:
        f.write(wrap(body))
    print('wrote', name, os.path.getsize(path))

# --- Chinese ---
FONT = "Microsoft YaHei, PingFang SC, sans-serif"

def cn1():
    items = [
        (147, "\u4ea7\u54c1\u6863\u6848\uff1a\u578b\u53f7\u3001\u8bc1\u4ef6\u3001UDI \u4e00\u6b21\u5efa"),
        (181, "\u6ce8\u518c\uff1a\u9501\u5b9a\u578b\u68c0\u5230\u53d6\u8bc1\u8def\u5f84"),
        (215, "\u51c6\u5165\uff1a\u6302\u7f51\u3001\u5e26\u91cf\u3001\u6295\u6807"),
        (249, "\u5165\u9662\uff1a\u533b\u9662\u5efa\u6863\u5230\u9996\u5355"),
        (283, "\u9636\u6bb5\u95e8\uff1a\u63d0\u4ea4\u3001\u6279\u51c6\u3001\u9a73\u56de\u7559\u75d5"),
        (317, "\u7ecf\u8425\u770b\u677f\uff1a\u5065\u5eb7\u5ea6\u3001\u8d39\u7528\u3001\u8bc1\u7167\u4e34\u671f"),
        (351, "\u5c97\u4f4d\u88c1\u526a\uff1a\u8d28\u7ba1/\u8fd0\u8425\u4e0d\u770b DevOps"),
        (385, "\u8431\u8431\u7d2b\uff1a\u548c\u5de5\u4f5c\u53f0\u540c\u4e00\u5957\u89c6\u89c9"),
    ]
    right = []
    for y, label in items:
        right.append(check(516, y - 5))
        right.append(t(532, y, "#3C4353", 13, label))
    return """
  <rect width="800" height="460" fill="#FCFDFE"/>
  <defs>
    <linearGradient id="jxBg" x1="0" y1="0" x2="480" y2="460" gradientUnits="userSpaceOnUse">
      <stop stop-color="#1E1850"/><stop offset="1" stop-color="#4A39B4"/>
    </linearGradient>
    <linearGradient id="jxCard" x1="36" y1="72" x2="444" y2="360" gradientUnits="userSpaceOnUse">
      <stop stop-color="#FFFFFF" stop-opacity=".16"/><stop offset="1" stop-color="#FFFFFF" stop-opacity=".06"/>
    </linearGradient>
  </defs>
  <rect width="480" height="460" fill="url(#jxBg)"/>
  <circle cx="420" cy="40" r="90" fill="#6C5CE7" fill-opacity=".28"/>
  <circle cx="60" cy="400" r="70" fill="#2DD4BF" fill-opacity=".16"/>
  <rect x="36" y="28" width="408" height="36" rx="10" fill="#FFFFFF" fill-opacity=".08"/>
  <circle cx="54" cy="46" r="8" fill="#2DD4BF"/>
  {brand}
  {ver}
  <rect x="36" y="80" width="408" height="88" rx="14" fill="url(#jxCard)" stroke="#FFFFFF" stroke-opacity=".18"/>
  {hi}
  {sub}
  <rect x="36" y="184" width="196" height="92" rx="12" fill="#FFFFFF" fill-opacity=".12"/>
  {p1}
  <text x="52" y="246" fill="#FFFFFF" font-family="{font}" font-size="28" font-weight="700">3</text>
  <rect x="248" y="184" width="196" height="92" rx="12" fill="#FFFFFF" fill-opacity=".12"/>
  {p2}
  <text x="264" y="246" fill="#FFFFFF" font-family="{font}" font-size="28" font-weight="700">5</text>
  <rect x="36" y="292" width="408" height="96" rx="12" fill="#FFFFFF" fill-opacity=".1"/>
  <rect x="52" y="310" width="8" height="8" rx="4" fill="#2DD4BF"/>
  {l1}
  <rect x="52" y="336" width="8" height="8" rx="4" fill="#FBBF24"/>
  {l2}
  <rect x="52" y="362" width="8" height="8" rx="4" fill="#FB7185"/>
  {l3}
  <rect x="56" y="404" width="220" height="28" rx="14" fill="#6C5CE7"/>
  {foot}
  {dots}
  {rt}
  {rs}
  {checks}
""".format(
        font=FONT,
        brand=t(70, 51, "#F4F3FF", 14, "\u5065\u5ffb\u533b\u7597\u9879\u76ee", "700"),
        ver=t(250, 51, "#D7D0FF", 12, "\u5de5\u4f5c\u53f0  1.0"),
        hi=t(56, 112, "#FFFFFF", 18, "\u6b22\u8fce\u4f7f\u7528\u5065\u5ffb 1.0", "700"),
        sub=t(56, 140, "#D7D0FF", 13, "\u6ce8\u518c\u3001\u51c6\u5165\u3001\u5165\u9662\uff0c\u4e00\u6761\u5de5\u4f5c\u53f0\u8d70\u5b8c"),
        p1=t(52, 212, "#B8ACFE", 12, "\u5f85\u5ba1\u6279"),
        p2=t(264, 212, "#B8ACFE", 12, "\u9700\u5173\u6ce8"),
        l1=t(68, 319, "#F4F3FF", 12, "\u6ce8\u518c  \u578b\u68c0\u5f85\u63d0\u4ea4"),
        l2=t(68, 345, "#F4F3FF", 12, "\u51c6\u5165  \u6295\u6807\u7a97\u53e3\u4e34\u8fd1"),
        l3=t(68, 371, "#F4F3FF", 12, "\u5165\u9662  \u9636\u6bb5\u95e8\u5f85\u5ba1"),
        foot=t(76, 423, "#FFFFFF", 12, "\u8431\u8431\u7d2b\u4e3b\u9898\uff0c\u5de5\u4f5c\u53f0\u4e00\u773c\u53ef\u8ba4"),
        dots=dots(0),
        rt=t(508, 72, "#0E0B23", 20, "\u5065\u5ffb 1.0\uff0c\u533b\u7597\u9879\u76ee\u5168\u6d41\u7a0b", "700"),
        rs=t(508, 104, "#5E626D", 13, "\u4ea7\u54c1\u6863\u6848\u3001\u9636\u6bb5\u95e8\u3001\u7ecf\u8425\u770b\u677f\uff0c\u96c6\u4e2d\u5728\u4e00\u4e2a\u5de5\u4f5c\u53f0\u3002"),
        checks="\n  ".join(right),
    )

def cn2():
    items = [
        (155, "\u4e09\u7c7b\u4e8b\u9879\u5404\u6709\u9501\u5b9a\u8def\u5f84\u548c\u9636\u6bb5\u95e8"),
        (195, "\u63d0\u4ea4\u540e\u8def\u5f84\u9501\u5b9a\uff0c\u907f\u514d\u8df3\u6b65"),
        (235, "\u6279\u51c6\u901a\u8fc7\u624d\u8fdb\u5165\u4e0b\u4e00\u9636\u6bb5"),
        (275, "\u9a73\u56de\u9000\u56de\u5e76\u4fdd\u7559\u5ba1\u6279\u610f\u89c1"),
        (315, "\u8d23\u4efb\u4eba\u3001\u65f6\u95f4\u3001\u7ed3\u8bba\u5168\u7a0b\u53ef\u67e5"),
        (355, "\u8fdb\u5ea6\u56de\u5230\u5de5\u4f5c\u53f0\uff0c\u5f85\u529e\u4e00\u773c\u53ef\u89c1"),
    ]
    right = []
    for y, label in items:
        right.append(check(516, y - 5))
        right.append(t(532, y, "#3C4353", 13, label))
    return """
  <rect width="800" height="460" fill="#FCFDFE"/>
  <defs>
    <linearGradient id="jxBg2" x1="0" y1="0" x2="480" y2="460" gradientUnits="userSpaceOnUse">
      <stop stop-color="#171338"/><stop offset="1" stop-color="#4A39B4"/>
    </linearGradient>
  </defs>
  <rect width="480" height="460" fill="url(#jxBg2)"/>
  <circle cx="40" cy="60" r="80" fill="#2DD4BF" fill-opacity=".12"/>
  {kicker}
  {title}
  <rect x="36" y="100" width="408" height="86" rx="14" fill="#FFFFFF" fill-opacity=".1"/>
  {a1}{a2}{a3}
  <rect x="36" y="198" width="408" height="86" rx="14" fill="#FFFFFF" fill-opacity=".1"/>
  {b1}{b2}{b3}
  <rect x="36" y="296" width="408" height="86" rx="14" fill="#FFFFFF" fill-opacity=".1"/>
  {c1}{c2}{c3}
  <rect x="56" y="404" width="248" height="28" rx="14" fill="#6C5CE7"/>
  {foot}
  {dots}
  {rt}{rs}
  {checks}
""".format(
        kicker=t(36, 48, "#D7D0FF", 13, "\u9636\u6bb5\u95e8"),
        title=t(36, 76, "#FFFFFF", 18, "\u5173\u952e\u8282\u70b9\u4e0d\u6f0f\u5ba1", "700"),
        a1=t(52, 126, "#2DD4BF", 12, "\u6ce8\u518c"),
        a2=t(52, 150, "#FFFFFF", 13, "\u7acb\u9879  -  \u578b\u68c0  -  \u7533\u62a5  -  \u53d6\u8bc1\u53d1\u8bc1"),
        a3=t(52, 172, "#B8ACFE", 12, "\u8865\u6b63  -  \u8865\u6d4b  -  \u8bc1\u4e66\u5f52\u6863"),
        b1=t(52, 224, "#FBBF24", 12, "\u51c6\u5165"),
        b2=t(52, 248, "#FFFFFF", 13, "\u6302\u7f51  -  \u5e26\u91cf\u91c7\u8d2d  -  \u6295\u6807\u62a5\u4ef7  -  \u4e2d\u6807\u7b7e\u7ea6"),
        b3=t(52, 270, "#B8ACFE", 12, "\u7a97\u53e3\u4e0d\u6f0f  -  \u8d44\u8d28\u9f50\u5957"),
        c1=t(52, 322, "#9384FB", 12, "\u5165\u9662"),
        c2=t(52, 346, "#FFFFFF", 13, "\u76ee\u6807\u9662  -  \u79d1\u5ba4\u51c6\u5165  -  \u5165\u9662\u9996\u5355"),
        c3=t(52, 368, "#B8ACFE", 12, "\u9662\u5185\u8ddf\u8fdb  -  \u91cf\u4ef7\u722c\u5761"),
        foot=t(76, 423, "#FFFFFF", 12, "\u63d0\u4ea4\u540e\u9501\u5b9a\u8def\u5f84\uff0c\u5ba1\u6279\u53ef\u8ffd\u6eaf"),
        dots=dots(1),
        rt=t(508, 72, "#0E0B23", 20, "\u9636\u6bb5\u95e8\u9a71\u52a8\uff0c\u5173\u952e\u8282\u70b9\u4e0d\u6f0f\u5ba1", "700"),
        rs=t(508, 104, "#5E626D", 13, "\u6bcf\u7c7b\u4e8b\u9879\u8d70\u81ea\u5df1\u7684\u8def\u5f84\uff0c\u63d0\u4ea4\u548c\u6279\u51c6\u90fd\u7559\u75d5\u3002"),
        checks="\n  ".join(right),
    )

def cn3():
    items = [
        (155, "\u7ec4\u5408\u5065\u5eb7\uff1a\u7eff\u9ec4\u7ea2\uff0c\u8d39\u7528\u8d85\u9608\u503c"),
        (195, "\u8bc1\u7167\u9884\u8b66\uff1a\u5230\u671f\u524d\u63d0\u9192\u6362\u8bc1"),
        (235, "\u6295\u6807\u7a97\u53e3\uff1a\u4e34\u8fd1\u7a97\u53e3\u4f18\u5148\u6392\u671f"),
        (275, "\u8d39\u7528\u900f\u89c6\uff1a\u9884\u7b97\u8d85\u652f\u7acb\u523b\u770b\u89c1"),
        (315, "\u5e26\u91cf\u91c7\u8d2d\uff1a\u6302\u7f51\u4e0e\u7eed\u7ea6\u4e0d\u9057\u6f0f"),
        (355, "\u5165\u9662\u6f0f\u6597\uff1a\u5efa\u6863\u5230\u9996\u5355\u53ef\u8ffd\u8e2a"),
    ]
    right = []
    for y, label in items:
        right.append(check(516, y - 5))
        right.append(t(532, y, "#3C4353", 13, label))
    return """
  <rect width="800" height="460" fill="#FCFDFE"/>
  <defs>
    <linearGradient id="jxBg3" x1="0" y1="0" x2="480" y2="460" gradientUnits="userSpaceOnUse">
      <stop stop-color="#1E1850"/><stop offset="1" stop-color="#322877"/>
    </linearGradient>
  </defs>
  <rect width="480" height="460" fill="url(#jxBg3)"/>
  {kicker}{title}
  <rect x="36" y="100" width="128" height="88" rx="14" fill="#FFFFFF" fill-opacity=".12"/>
  {g}{gn}
  <rect x="176" y="100" width="128" height="88" rx="14" fill="#FFFFFF" fill-opacity=".12"/>
  {y}{yn}
  <rect x="316" y="100" width="128" height="88" rx="14" fill="#FFFFFF" fill-opacity=".12"/>
  {r}{rn}
  <rect x="36" y="204" width="408" height="168" rx="14" fill="#FFFFFF" fill-opacity=".1"/>
  {w}{w1}{w2}{w3}{w4}
  <rect x="56" y="404" width="232" height="28" rx="14" fill="#6C5CE7"/>
  {foot}
  {dots}
  {rt}{rs}
  {checks}
""".format(
        kicker=t(36, 48, "#D7D0FF", 13, "\u7ecf\u8425\u770b\u677f"),
        title=t(36, 76, "#FFFFFF", 18, "\u7ec4\u5408\u5065\u5eb7\uff0c\u98ce\u9669\u63d0\u524d\u770b\u89c1", "700"),
        g=t(52, 128, "#86EFAC", 12, "\u7eff"),
        gn='<text x="52" y="164" fill="#FFFFFF" font-family="%s" font-size="28" font-weight="700">12</text>' % FONT,
        y=t(192, 128, "#FDE68A", 12, "\u9ec4"),
        yn='<text x="192" y="164" fill="#FFFFFF" font-family="%s" font-size="28" font-weight="700">4</text>' % FONT,
        r=t(332, 128, "#FDA4AF", 12, "\u7ea2"),
        rn='<text x="332" y="164" fill="#FFFFFF" font-family="%s" font-size="28" font-weight="700">2</text>' % FONT,
        w=t(52, 232, "#B8ACFE", 12, "\u672c\u5468\u5173\u6ce8"),
        w1=t(52, 262, "#FFFFFF", 14, "\u8bc1\u7167 3 \u4efd\uff0c90 \u5929\u5185\u5230\u671f"),
        w2=t(52, 290, "#FFFFFF", 14, "2 \u4e2a\u6295\u6807\u7a97\u53e3\u4e0d\u8db3 14 \u5929"),
        w3=t(52, 318, "#FFFFFF", 14, "\u5165\u9662\u6f0f\u6597\uff1a\u5efa\u6863 18  /  \u51c6\u5165 7  /  \u9996\u5355 3"),
        w4=t(52, 346, "#D7D0FF", 13, "\u6309\u90e8\u95e8\u8fc7\u6ee4\u8d39\u7528\u5dee\u5f02\u548c\u5835\u70b9"),
        foot=t(76, 423, "#FFFFFF", 12, "\u7ea2\u9ec4\u7eff\u5065\u5eb7\u5ea6\uff0c\u7ec4\u5408\u4e00\u76ee\u4e86\u7136"),
        dots=dots(2),
        rt=t(508, 72, "#0E0B23", 20, "\u7ecf\u8425\u770b\u677f\uff0c\u7ec4\u5408\u98ce\u9669\u63d0\u524d\u770b\u89c1", "700"),
        rs=t(508, 104, "#5E626D", 13, "\u5065\u5eb7\u5ea6\u3001\u8d39\u7528\u3001\u8bc1\u7167\u4e34\u671f\u3001\u6295\u6807\u7a97\u53e3\u5728\u4e00\u5f20\u770b\u677f\u3002"),
        checks="\n  ".join(right),
    )

def cn4():
    items = [
        (155, "\u4ea7\u54c1\uff1a\u5148\u5efa\u6863\u6848\u518d\u6302\u4e8b\u9879"),
        (195, "\u6ce8\u518c\uff1a\u9501\u5b9a\u53d6\u8bc1\u8def\u5f84"),
        (235, "\u51c6\u5165\uff1a\u6302\u7f51\u3001\u5e26\u91cf\u3001\u6295\u6807"),
        (275, "\u5165\u9662\uff1a\u533b\u9662\u5efa\u6863\u5230\u9996\u5355"),
        (315, "\u5de5\u4f5c\u53f0\uff1a\u5f85\u529e\u548c\u903e\u671f\u4e00\u773c\u770b\u5230"),
        (355, "\u770b\u677f\uff1a\u7ec4\u5408\u5065\u5eb7\u4e0e\u8d39\u7528\u900f\u89c6"),
    ]
    right = []
    for y, label in items:
        right.append(check(516, y - 5))
        right.append(t(532, y, "#3C4353", 13, label))
    return """
  <rect width="800" height="460" fill="#FCFDFE"/>
  <defs>
    <linearGradient id="jxBg4" x1="0" y1="0" x2="480" y2="460" gradientUnits="userSpaceOnUse">
      <stop stop-color="#221C4F"/><stop offset="1" stop-color="#5B49D6"/>
    </linearGradient>
  </defs>
  <rect width="480" height="460" fill="url(#jxBg4)"/>
  {kicker}{title}
  <rect x="36" y="104" width="408" height="80" rx="14" fill="#FFFFFF" fill-opacity=".12"/>
  <circle cx="68" cy="144" r="16" fill="#6C5CE7"/>
  <text x="63" y="149" fill="#FFFFFF" font-family="{font}" font-size="16" font-weight="700">1</text>
  {s1}{s1d}
  <rect x="36" y="196" width="408" height="80" rx="14" fill="#FFFFFF" fill-opacity=".12"/>
  <circle cx="68" cy="236" r="16" fill="#6C5CE7"/>
  <text x="63" y="241" fill="#FFFFFF" font-family="{font}" font-size="16" font-weight="700">2</text>
  {s2}{s2d}
  <rect x="36" y="288" width="408" height="80" rx="14" fill="#FFFFFF" fill-opacity=".12"/>
  <circle cx="68" cy="328" r="16" fill="#2DD4BF"/>
  <text x="63" y="333" fill="#0E0B23" font-family="{font}" font-size="16" font-weight="700">3</text>
  {s3}{s3d}
  <rect x="56" y="404" width="268" height="28" rx="14" fill="#6C5CE7"/>
  {foot}
  {dots}
  {rt}{rs}
  {checks}
""".format(
        font=FONT,
        kicker=t(36, 48, "#D7D0FF", 13, "\u5f00\u59cb"),
        title=t(36, 76, "#FFFFFF", 18, "\u4e09\u6b65\u628a\u4e8b\u9879\u63a8\u8fc7\u9636\u6bb5\u95e8", "700"),
        s1=t(96, 136, "#FFFFFF", 15, "\u5efa\u7acb\u4ea7\u54c1\u6863\u6848", "700"),
        s1d=t(96, 160, "#D7D0FF", 12, "\u578b\u53f7\u3001\u89c4\u683c\u3001\u8bc1\u4ef6\u3001\u533b\u9662\u4e0e UDI"),
        s2=t(96, 228, "#FFFFFF", 15, "\u521b\u5efa\u4e09\u7c7b\u4e8b\u9879", "700"),
        s2d=t(96, 252, "#D7D0FF", 12, "\u6ce8\u518c / \u51c6\u5165 / \u5165\u9662\uff0c\u7cfb\u7edf\u7ed9\u51fa\u9501\u5b9a\u8def\u5f84"),
        s3=t(96, 320, "#FFFFFF", 15, "\u63a8\u8fdb\u9636\u6bb5\u95e8\uff0c\u770b\u677f\u66f4\u65b0", "700"),
        s3d=t(96, 344, "#D7D0FF", 12, "\u63d0\u4ea4\u5ba1\u6279\u3001\u9a73\u56de\u91cd\u63d0\uff0c\u8fdb\u5ea6\u56de\u5230\u5de5\u4f5c\u53f0"),
        foot=t(76, 423, "#FFFFFF", 12, "\u4ece\u5efa\u6863  \u5230  \u5efa\u4e8b\u9879  \u5230  \u63a8\u9636\u6bb5\u95e8"),
        dots=dots(3),
        rt=t(508, 72, "#0E0B23", 20, "\u4e09\u6b65\u5f00\u59cb\uff0c\u9a6c\u4e0a\u80fd\u7528", "700"),
        rs=t(508, 104, "#5E626D", 13, "\u4ece\u4ea7\u54c1\u6863\u6848\u5efa\u5230\u9636\u6bb5\u95e8\u63a8\u8fdb\uff0c\u8fdb\u5ea6\u56de\u5230\u8fd9\u91cc\u3002"),
        checks="\n  ".join(right),
    )

# --- English ---
FONT_EN = "Segoe UI, sans-serif"

def t_en(*a, **k):
    global FONT
    old, FONT = FONT, FONT_EN
    try:
        return t(*a, **k)
    finally:
        FONT = old

def en1():
    items = [
        (147, "Product file: model, certificate, and UDI"),
        (181, "Registration: lock the path through certification"),
        (215, "Market access: listing, volume-based, bidding"),
        (249, "Admission: hospital file to first order"),
        (283, "Stage gates: submit, approve, or reject with a trail"),
        (317, "Portfolio board: health, cost, expiring certs"),
        (351, "Role-based menus: QA and DevOps hidden for ops"),
        (385, "Purple theme aligned with the workbench"),
    ]
    right = []
    for y, label in items:
        right.append(check(516, y - 5))
        right.append(t_en(532, y, "#3C4353", 13, label))
    return """
  <rect width="800" height="460" fill="#FCFDFE"/>
  <defs>
    <linearGradient id="jxBg" x1="0" y1="0" x2="480" y2="460" gradientUnits="userSpaceOnUse">
      <stop stop-color="#1E1850"/><stop offset="1" stop-color="#4A39B4"/>
    </linearGradient>
    <linearGradient id="jxCard" x1="36" y1="72" x2="444" y2="360" gradientUnits="userSpaceOnUse">
      <stop stop-color="#FFFFFF" stop-opacity=".16"/><stop offset="1" stop-color="#FFFFFF" stop-opacity=".06"/>
    </linearGradient>
  </defs>
  <rect width="480" height="460" fill="url(#jxBg)"/>
  <circle cx="420" cy="40" r="90" fill="#6C5CE7" fill-opacity=".28"/>
  <circle cx="60" cy="400" r="70" fill="#2DD4BF" fill-opacity=".16"/>
  <rect x="36" y="28" width="408" height="36" rx="10" fill="#FFFFFF" fill-opacity=".08"/>
  <circle cx="54" cy="46" r="8" fill="#2DD4BF"/>
  {brand}{ver}
  <rect x="36" y="80" width="408" height="88" rx="14" fill="url(#jxCard)" stroke="#FFFFFF" stroke-opacity=".18"/>
  {hi}{sub}
  <rect x="36" y="184" width="196" height="92" rx="12" fill="#FFFFFF" fill-opacity=".12"/>
  {p1}<text x="52" y="246" fill="#FFFFFF" font-family="{font}" font-size="28" font-weight="700">3</text>
  <rect x="248" y="184" width="196" height="92" rx="12" fill="#FFFFFF" fill-opacity=".12"/>
  {p2}<text x="264" y="246" fill="#FFFFFF" font-family="{font}" font-size="28" font-weight="700">5</text>
  <rect x="36" y="292" width="408" height="96" rx="12" fill="#FFFFFF" fill-opacity=".1"/>
  <rect x="52" y="310" width="8" height="8" rx="4" fill="#2DD4BF"/>{l1}
  <rect x="52" y="336" width="8" height="8" rx="4" fill="#FBBF24"/>{l2}
  <rect x="52" y="362" width="8" height="8" rx="4" fill="#FB7185"/>{l3}
  <rect x="56" y="404" width="248" height="28" rx="14" fill="#6C5CE7"/>{foot}
  {dots}{rt}{rs}{checks}
""".format(
        font=FONT_EN,
        brand=t_en(70, 51, "#F4F3FF", 14, "Jenshin Medical PM", "700"),
        ver=t_en(250, 51, "#D7D0FF", 12, "Workbench  1.0"),
        hi=t_en(56, 112, "#FFFFFF", 18, "Welcome to Jenshin 1.0", "700"),
        sub=t_en(56, 140, "#D7D0FF", 13, "Registration, access, and admission in one workspace"),
        p1=t_en(52, 212, "#B8ACFE", 12, "Pending review"),
        p2=t_en(264, 212, "#B8ACFE", 12, "Needs attention"),
        l1=t_en(68, 319, "#F4F3FF", 12, "Registration  type testing ready to submit"),
        l2=t_en(68, 345, "#F4F3FF", 12, "Market access  bidding window approaching"),
        l3=t_en(68, 371, "#F4F3FF", 12, "Hospital admission  stage gate pending"),
        foot=t_en(76, 423, "#FFFFFF", 12, "Purple theme, the workbench is unmistakable"),
        dots=dots(0),
        rt=t_en(508, 72, "#0E0B23", 20, "Jenshin 1.0 for medical programs", "700"),
        rs=t_en(508, 104, "#5E626D", 13, "Product files, stage gates, and a portfolio board in one workspace."),
        checks="\n  ".join(right),
    )

def en2():
    items = [
        (155, "Creating a matter builds project, sprint, and tasks"),
        (195, "Checklists bind to each stage"),
        (235, "Submit a gate to pending review, or reject it"),
        (275, "Approval comments stay on the record"),
        (315, "Blockers roll up under Needs attention"),
        (355, "Docs stay with the matter, not scattered"),
    ]
    right = []
    for y, label in items:
        right.append(check(516, y - 5))
        right.append(t_en(532, y, "#3C4353", 13, label))
    return """
  <rect width="800" height="460" fill="#FCFDFE"/>
  <defs>
    <linearGradient id="jxBg2" x1="0" y1="0" x2="480" y2="460" gradientUnits="userSpaceOnUse">
      <stop stop-color="#171338"/><stop offset="1" stop-color="#4A39B4"/>
    </linearGradient>
  </defs>
  <rect width="480" height="460" fill="url(#jxBg2)"/>
  {kicker}{title}
  <rect x="36" y="100" width="408" height="86" rx="14" fill="#FFFFFF" fill-opacity=".1"/>
  {a1}{a2}{a3}
  <rect x="36" y="198" width="408" height="86" rx="14" fill="#FFFFFF" fill-opacity=".1"/>
  {b1}{b2}{b3}
  <rect x="36" y="296" width="408" height="86" rx="14" fill="#FFFFFF" fill-opacity=".1"/>
  {c1}{c2}{c3}
  <rect x="56" y="404" width="268" height="28" rx="14" fill="#6C5CE7"/>{foot}
  {dots}{rt}{rs}{checks}
""".format(
        kicker=t_en(36, 48, "#D7D0FF", 13, "Stage-gate paths"),
        title=t_en(36, 76, "#FFFFFF", 18, "One matter type, one locked path", "700"),
        a1=t_en(52, 126, "#2DD4BF", 12, "Registration"),
        a2=t_en(52, 150, "#FFFFFF", 13, "Path lock - type test - clinical - QMS"),
        a3=t_en(52, 172, "#B8ACFE", 12, "Submit - supplement - archive the certificate"),
        b1=t_en(52, 224, "#FBBF24", 12, "Market access"),
        b2=t_en(52, 248, "#FFFFFF", 13, "Lead - capability - quote - bid"),
        b3=t_en(52, 270, "#B8ACFE", 12, "Award - contract - allocation"),
        c1=t_en(52, 322, "#9384FB", 12, "Hospital admission"),
        c2=t_en(52, 346, "#FFFFFF", 13, "Target - in-hospital access - coaching"),
        c3=t_en(52, 368, "#B8ACFE", 12, "First order - ramp-up"),
        foot=t_en(76, 423, "#FFFFFF", 12, "Checklists expand after the path is locked"),
        dots=dots(1),
        rt=t_en(508, 72, "#0E0B23", 20, "Stage gates, no skipped reviews", "700"),
        rs=t_en(508, 104, "#5E626D", 13, "Each matter follows its path. Submit and approve with a trail."),
        checks="\n  ".join(right),
    )

def en3():
    items = [
        (155, "Overview: count, budget, and actual spend"),
        (195, "Departments: load and cost variance"),
        (235, "Portfolio: filter by health color"),
        (275, "Certificates: remind before they expire"),
        (315, "Windows: listing and tender dates"),
        (355, "Funnel: from screening to first order"),
    ]
    right = []
    for y, label in items:
        right.append(check(516, y - 5))
        right.append(t_en(532, y, "#3C4353", 13, label))
    return """
  <rect width="800" height="460" fill="#FCFDFE"/>
  <defs>
    <linearGradient id="jxBg3" x1="0" y1="0" x2="480" y2="460" gradientUnits="userSpaceOnUse">
      <stop stop-color="#1E1850"/><stop offset="1" stop-color="#322877"/>
    </linearGradient>
  </defs>
  <rect width="480" height="460" fill="url(#jxBg3)"/>
  {kicker}{title}
  <rect x="36" y="100" width="128" height="88" rx="14" fill="#FFFFFF" fill-opacity=".12"/>
  {g}<text x="52" y="164" fill="#FFFFFF" font-family="{font}" font-size="28" font-weight="700">12</text>
  <rect x="176" y="100" width="128" height="88" rx="14" fill="#FFFFFF" fill-opacity=".12"/>
  {y}<text x="192" y="164" fill="#FFFFFF" font-family="{font}" font-size="28" font-weight="700">4</text>
  <rect x="316" y="100" width="128" height="88" rx="14" fill="#FFFFFF" fill-opacity=".12"/>
  {r}<text x="332" y="164" fill="#FFFFFF" font-family="{font}" font-size="28" font-weight="700">2</text>
  <rect x="36" y="204" width="408" height="168" rx="14" fill="#FFFFFF" fill-opacity=".1"/>
  {w}{w1}{w2}{w3}{w4}
  <rect x="56" y="404" width="250" height="28" rx="14" fill="#6C5CE7"/>{foot}
  {dots}{rt}{rs}{checks}
""".format(
        font=FONT_EN,
        kicker=t_en(36, 48, "#D7D0FF", 13, "Portfolio board"),
        title=t_en(36, 76, "#FFFFFF", 18, "Green, yellow, red at a glance", "700"),
        g=t_en(52, 128, "#86EFAC", 12, "Healthy"),
        y=t_en(192, 128, "#FDE68A", 12, "Watch"),
        r=t_en(332, 128, "#FDA4AF", 12, "Risk"),
        w=t_en(52, 232, "#B8ACFE", 12, "This week"),
        w1=t_en(52, 262, "#FFFFFF", 14, "3 certificates expire within 90 days"),
        w2=t_en(52, 290, "#FFFFFF", 14, "2 bidding windows need a quote review"),
        w3=t_en(52, 318, "#FFFFFF", 14, "Admission funnel: 18 / 7 / 3 first orders"),
        w4=t_en(52, 346, "#D7D0FF", 13, "Filter cost variance and blockers by department"),
        foot=t_en(76, 423, "#FFFFFF", 12, "Health colors across the portfolio"),
        dots=dots(2),
        rt=t_en(508, 72, "#0E0B23", 20, "See portfolio risk earlier", "700"),
        rs=t_en(508, 104, "#5E626D", 13, "Health, spend, expiring certs, and bid windows in one board."),
        checks="\n  ".join(right),
    )

def en4():
    items = [
        (155, "Products: keep the medical file and link matters"),
        (195, "Registration: walk the path to the certificate"),
        (235, "Access: listing, volume-based, and bid windows"),
        (275, "Admission: hospital file through first order"),
        (315, "Workbench: reviews and overdue items in one place"),
        (355, "Board: health and cost variance across programs"),
    ]
    right = []
    for y, label in items:
        right.append(check(516, y - 5))
        right.append(t_en(532, y, "#3C4353", 13, label))
    return """
  <rect width="800" height="460" fill="#FCFDFE"/>
  <defs>
    <linearGradient id="jxBg4" x1="0" y1="0" x2="480" y2="460" gradientUnits="userSpaceOnUse">
      <stop stop-color="#221C4F"/><stop offset="1" stop-color="#5B49D6"/>
    </linearGradient>
  </defs>
  <rect width="480" height="460" fill="url(#jxBg4)"/>
  {kicker}{title}
  <rect x="36" y="104" width="408" height="80" rx="14" fill="#FFFFFF" fill-opacity=".12"/>
  <circle cx="68" cy="144" r="16" fill="#6C5CE7"/>
  <text x="63" y="149" fill="#FFFFFF" font-family="{font}" font-size="16" font-weight="700">1</text>
  {s1}{s1d}
  <rect x="36" y="196" width="408" height="80" rx="14" fill="#FFFFFF" fill-opacity=".12"/>
  <circle cx="68" cy="236" r="16" fill="#6C5CE7"/>
  <text x="63" y="241" fill="#FFFFFF" font-family="{font}" font-size="16" font-weight="700">2</text>
  {s2}{s2d}
  <rect x="36" y="288" width="408" height="80" rx="14" fill="#FFFFFF" fill-opacity=".12"/>
  <circle cx="68" cy="328" r="16" fill="#2DD4BF"/>
  <text x="63" y="333" fill="#0E0B23" font-family="{font}" font-size="16" font-weight="700">3</text>
  {s3}{s3d}
  <rect x="56" y="404" width="288" height="28" rx="14" fill="#6C5CE7"/>{foot}
  {dots}{rt}{rs}{checks}
""".format(
        font=FONT_EN,
        kicker=t_en(36, 48, "#D7D0FF", 13, "Get started"),
        title=t_en(36, 76, "#FFFFFF", 18, "Three steps into the first pipeline", "700"),
        s1=t_en(96, 136, "#FFFFFF", 15, "Create the product file", "700"),
        s1d=t_en(96, 160, "#D7D0FF", 12, "Model, class, certificate, expiry, and UDI"),
        s2=t_en(96, 228, "#FFFFFF", 15, "Open a matter by business type", "700"),
        s2d=t_en(96, 252, "#D7D0FF", 12, "Registration, access, or admission - stages are generated"),
        s3=t_en(96, 320, "#FFFFFF", 15, "Move the gates; the board updates", "700"),
        s3d=t_en(96, 344, "#D7D0FF", 12, "Finish checks, submit review, health returns to the workbench"),
        foot=t_en(76, 423, "#FFFFFF", 12, "A matter creates the project and stage tasks"),
        dots=dots(3),
        rt=t_en(508, 72, "#0E0B23", 20, "You can start now", "700"),
        rs=t_en(508, 104, "#5E626D", 13, "From the product file to stage progress, status returns here."),
        checks="\n  ".join(right),
    )

if __name__ == "__main__":
    write("cn_guide1_1_0.svg", cn1())
    write("cn_guide2_1_0.svg", cn2())
    write("cn_guide3_1_0.svg", cn3())
    write("cn_guide4_1_0.svg", cn4())
    write("en_guide1_1_0.svg", en1())
    write("en_guide2_1_0.svg", en2())
    write("en_guide3_1_0.svg", en3())
    write("en_guide4_1_0.svg", en4())
