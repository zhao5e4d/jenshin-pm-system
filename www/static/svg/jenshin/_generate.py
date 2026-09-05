# -*- coding: utf-8 -*-
"""Generate UTF-8 guide SVGs. Run: python _generate.py"""
from __future__ import print_function
from xml.sax.saxutils import escape
import os

OUT = os.path.dirname(os.path.abspath(__file__))
CN_FONT = "Microsoft YaHei, PingFang SC, sans-serif"
EN_FONT = "Segoe UI, sans-serif"


def t(x, y, fill, size, text, weight=None, family=None):
    fw = ' font-weight="%s"' % weight if weight else ""
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
        op = "" if i == active else ' fill-opacity=".35"'
        parts.append('<circle cx="%s" cy="444" r="4" fill="#FFFFFF"%s/>' % (x, op))
    return "\n  ".join(parts)


def wrap(inner):
    return (
        '<?xml version="1.0" encoding="UTF-8"?>\n'
        '<svg width="800" height="460" viewBox="0 0 800 460" fill="none" '
        'xmlns="http://www.w3.org/2000/svg">\n%s\n</svg>\n' % inner
    )


def write(name, body):
    path = os.path.join(OUT, name)
    with open(path, "w", encoding="utf-8", newline="\n") as f:
        f.write(wrap(body))
    raw = open(path, "rb").read()
    raw.decode("utf-8")
    print("wrote", name, os.path.getsize(path))


def rights(items):
    out = []
    for y, label in items:
        out.append(check(516, y - 5))
        out.append(t(532, y, "#3C4353", 13, label))
    return "\n  ".join(out)


# --- Chinese ---
FONT = CN_FONT


def cn1():
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
  <rect x="36" y="80" width="408" height="72" rx="14" fill="url(#jxCard)" stroke="#FFFFFF" stroke-opacity=".18"/>
  {hi}{sub}
  <rect x="36" y="164" width="196" height="68" rx="12" fill="#FFFFFF" fill-opacity=".12"/>
  {c1}<text x="52" y="218" fill="#FFFFFF" font-family="{font}" font-size="24" font-weight="700">2</text>
  <rect x="248" y="164" width="196" height="68" rx="12" fill="#FFFFFF" fill-opacity=".12"/>
  {c2}<text x="264" y="218" fill="#FFFFFF" font-family="{font}" font-size="24" font-weight="700">3</text>
  <rect x="36" y="240" width="196" height="68" rx="12" fill="#FFFFFF" fill-opacity=".12"/>
  {c3}<text x="52" y="294" fill="#FFFFFF" font-family="{font}" font-size="24" font-weight="700">1</text>
  <rect x="248" y="240" width="196" height="68" rx="12" fill="#FFFFFF" fill-opacity=".12"/>
  {c4}<text x="264" y="294" fill="#FFFFFF" font-family="{font}" font-size="24" font-weight="700">1</text>
  <rect x="36" y="320" width="408" height="72" rx="12" fill="#FFFFFF" fill-opacity=".1"/>
  <rect x="52" y="336" width="8" height="8" rx="4" fill="#2DD4BF"/>
  {l1}
  <rect x="52" y="356" width="8" height="8" rx="4" fill="#FBBF24"/>
  {l2}
  <rect x="52" y="376" width="8" height="8" rx="4" fill="#FB7185"/>
  {l3}
  <rect x="56" y="404" width="232" height="28" rx="14" fill="#6C5CE7"/>
  {foot}
  {dots}
  {rt}{rs}
  {checks}
""".format(
        font=FONT,
        brand=t(70, 51, "#F4F3FF", 14, "\u5065\u5ffb\u533b\u7597\u9879\u76ee", "700"),
        ver=t(250, 51, "#D7D0FF", 12, "\u5de5\u4f5c\u53f0  1.0"),
        hi=t(56, 108, "#FFFFFF", 18, "\u6b22\u8fce\u4f7f\u7528\u5065\u5ffb 1.0", "700"),
        sub=t(56, 134, "#D7D0FF", 13, "\u5148\u770b\u9700\u5173\u6ce8\uff0c\u518d\u8fdb\u5177\u4f53\u9879\u76ee"),
        c1=t(52, 188, "#B8ACFE", 12, "\u5f85\u529e\u65e5\u7a0b"),
        c2=t(264, 188, "#B8ACFE", 12, "\u5f85\u5904\u7406\u4efb\u52a1"),
        c3=t(52, 264, "#B8ACFE", 12, "\u903e\u671f\u9879\u76ee"),
        c4=t(264, 264, "#B8ACFE", 12, "\u98ce\u9669\u9879\u76ee"),
        l1=t(68, 345, "#F4F3FF", 12, "\u5f85\u5904\u7406\u4efb\u52a1  \u9a7e\u9a76\u8231\u4e0e\u9884\u7b97\u5ba1\u6279"),
        l2=t(68, 365, "#F4F3FF", 12, "\u903e\u671f\u9879\u76ee  \u5b9e\u9a8c\u5ba4\u7ba1\u7406\u7cfb\u7edf"),
        l3=t(68, 385, "#F4F3FF", 12, "\u8bc1\u7167\u4e34\u671f  \u6709\u6548\u671f\u4e0d\u8db3 90 \u5929"),
        foot=t(76, 423, "#FFFFFF", 12, "\u5de5\u4f5c\u53f0\u4e00\u773c\u770b\u5230\u8be5\u505a\u4ec0\u4e48"),
        dots=dots(0),
        rt=t(508, 72, "#0E0B23", 20, "\u5065\u5ffb 1.0\uff0c\u533b\u7597\u9879\u76ee\u7ba1\u7406", "700"),
        rs=t(508, 104, "#5E626D", 13, "\u4ea7\u54c1\u3001\u9879\u76ee\u3001\u9636\u6bb5\u4efb\u52a1\u548c\u770b\u677f\uff0c\u96c6\u4e2d\u5728\u4e00\u4e2a\u5de5\u4f5c\u53f0\u3002"),
        checks=rights([
            (147, "\u5de5\u4f5c\u53f0\uff1a\u770b\u5f85\u529e\u3001\u903e\u671f\u4e0e\u98ce\u9669"),
            (181, "\u4ea7\u54c1\u7ec4\u5408\uff1a\u7ef4\u62a4\u6863\u6848\u4e0e\u4e34\u671f"),
            (215, "\u9879\u76ee\u7ba1\u7406\uff1a\u521b\u5efa\u5e76\u5173\u8054\u4ea7\u54c1"),
            (249, "\u9636\u6bb5\u4efb\u52a1\uff1a\u5efa\u4efb\u52a1\u5e76\u63a8\u8fdb"),
            (283, "\u6570\u636e\u770b\u677f\uff1a\u5065\u5eb7\u5ea6\u4e0e\u903e\u671f"),
            (317, "\u6587\u6863\u7a7a\u95f4\uff1a\u5f52\u6863\u8fc7\u7a0b\u8d44\u6599"),
            (351, "\u7ec4\u7ec7\u90e8\u95e8\uff1a\u67e5\u770b\u540c\u4e8b\u5f52\u5c5e"),
        ]),
    )


def cn2():
    return """
  <rect width="800" height="460" fill="#FCFDFE"/>
  <defs>
    <linearGradient id="jxBg2" x1="0" y1="0" x2="480" y2="460" gradientUnits="userSpaceOnUse">
      <stop stop-color="#171338"/><stop offset="1" stop-color="#4A39B4"/>
    </linearGradient>
  </defs>
  <rect width="480" height="460" fill="url(#jxBg2)"/>
  <circle cx="40" cy="60" r="80" fill="#2DD4BF" fill-opacity=".12"/>
  {kicker}{title}
  <rect x="36" y="100" width="408" height="196" rx="14" fill="#FFFFFF" fill-opacity=".1"/>
  {list}{row1}
  <rect x="320" y="140" width="40" height="18" rx="9" fill="#FBBF24"/>
  {soon}{row2}{meta}{hint}{note}
  <rect x="36" y="308" width="196" height="72" rx="14" fill="#FFFFFF" fill-opacity=".12"/>
  {p1}{p1d}
  <rect x="248" y="308" width="196" height="72" rx="14" fill="#FFFFFF" fill-opacity=".12"/>
  {p2}{p2d}
  <rect x="56" y="404" width="248" height="28" rx="14" fill="#6C5CE7"/>
  {foot}
  {dots}
  {rt}{rs}
  {checks}
""".format(
        kicker=t(36, 48, "#D7D0FF", 13, "\u4ea7\u54c1\u7ec4\u5408"),
        title=t(36, 76, "#FFFFFF", 18, "\u5148\u5efa\u6863\u6848\uff0c\u518d\u6302\u9879\u76ee", "700"),
        list=t(52, 126, "#B8ACFE", 12, "\u4ea7\u54c1\u5217\u8868"),
        row1=t(52, 154, "#FFFFFF", 13, "JX-WS80    \u4e8c\u7c7b    \u82cf\u68b0\u6ce8\u51c6 2026"),
        soon=t(326, 153, "#0E0B23", 11, "\u4e34\u671f", "700"),
        row2=t(52, 182, "#FFFFFF", 13, "JX-BB18    \u4e09\u7c7b    \u82cf\u68b0\u6ce8\u51c6 2025"),
        meta=t(52, 210, "#D7D0FF", 12, "\u578b\u53f7 \u00b7 \u7ba1\u7406\u7c7b\u522b \u00b7 \u6ce8\u518c\u8bc1\u53f7 \u00b7 \u6709\u6548\u671f"),
        hint=t(52, 236, "#D7D0FF", 12, "90 \u5929\u5185\u5230\u671f\uff0c\u5217\u8868\u76f4\u63a5\u6807\u6a59\u8272\u300c\u4e34\u671f\u300d"),
        note=t(52, 270, "#B8ACFE", 12, "\u5efa\u6e05\u695a\u518d\u6302\u9879\u76ee\uff0c\u8bc1\u7167\u548c\u8fdb\u5ea6\u5bf9\u5f97\u4e0a"),
        p1=t(52, 336, "#2DD4BF", 12, "\u521b\u5efa\u9879\u76ee"),
        p1d=t(52, 362, "#FFFFFF", 13, "\u4ea7\u54c1\u578b  /  \u9879\u76ee\u578b"),
        p2=t(264, 336, "#9384FB", 12, "\u6dfb\u52a0\u9636\u6bb5"),
        p2d=t(264, 362, "#FFFFFF", 13, "\u77ed\u671f  /  \u957f\u671f  /  \u8fd0\u7ef4"),
        foot=t(76, 423, "#FFFFFF", 12, "\u6863\u6848\u6e05\u695a\uff0c\u9879\u76ee\u624d\u80fd\u5bf9\u4e0a\u8fdb\u5ea6"),
        dots=dots(1),
        rt=t(508, 72, "#0E0B23", 20, "\u5148\u5efa\u6863\u6848\uff0c\u518d\u6302\u9879\u76ee\u548c\u9636\u6bb5", "700"),
        rs=t(508, 104, "#5E626D", 13, "\u4ea7\u54c1\u5efa\u6e05\u695a\uff0c\u9879\u76ee\u624d\u80fd\u5bf9\u4e0a\u8bc1\u7167\u548c\u8fdb\u5ea6\u3002"),
        checks=rights([
            (155, "\u6863\u6848\uff1a\u578b\u53f7\u3001\u7c7b\u522b\u3001\u8bc1\u53f7\u3001\u6709\u6548\u671f"),
            (195, "\u8bc1\u7167 90 \u5929\u5185\u5230\u671f\u6807\u300c\u4e34\u671f\u300d"),
            (235, "\u521b\u5efa\u9879\u76ee\uff1a\u9009\u4ea7\u54c1\u578b\u6216\u9879\u76ee\u578b"),
            (275, "\u5c3d\u91cf\u5173\u8054\u5df2\u5efa\u6863\u7684\u4ea7\u54c1"),
            (315, "\u9879\u76ee\u4e0b\u6dfb\u52a0\u9636\u6bb5\uff08\u77ed/\u957f/\u8fd0\u7ef4\uff09"),
            (355, "\u300c\u9636\u6bb5\u4efb\u52a1\u300d\u53ef\u770b\u5168\u90e8\u9636\u6bb5"),
        ]),
    )


def cn3():
    return """
  <rect width="800" height="460" fill="#FCFDFE"/>
  <defs>
    <linearGradient id="jxBg3" x1="0" y1="0" x2="480" y2="460" gradientUnits="userSpaceOnUse">
      <stop stop-color="#1E1850"/><stop offset="1" stop-color="#322877"/>
    </linearGradient>
  </defs>
  <rect width="480" height="460" fill="url(#jxBg3)"/>
  {kicker}{title}
  <rect x="36" y="100" width="408" height="272" rx="14" fill="#FFFFFF" fill-opacity=".1"/>
  {stage}
  <rect x="340" y="110" width="80" height="24" rx="12" fill="#6C5CE7"/>
  {newbtn}
  <rect x="52" y="148" width="376" height="56" rx="10" fill="#FFFFFF" fill-opacity=".08"/>
  {t1}{t1d}
  <rect x="52" y="214" width="376" height="56" rx="10" fill="#FFFFFF" fill-opacity=".08"/>
  {t2}{t2d}
  <rect x="52" y="280" width="376" height="56" rx="10" fill="#FFFFFF" fill-opacity=".08"/>
  {t3}{t3d}
  <rect x="56" y="404" width="248" height="28" rx="14" fill="#6C5CE7"/>
  {foot}
  {dots}
  {rt}{rs}
  {checks}
""".format(
        kicker=t(36, 48, "#D7D0FF", 13, "\u9636\u6bb5\u4efb\u52a1"),
        title=t(36, 76, "#FFFFFF", 18, "\u65e5\u5e38\u5728\u9636\u6bb5\u91cc\u628a\u4efb\u52a1\u505a\u5b8c", "700"),
        stage=t(52, 128, "#B8ACFE", 12, "\u9636\u6bb5 V1.0"),
        newbtn=t(354, 127, "#FFFFFF", 12, "\u5efa\u4efb\u52a1"),
        t1=t(68, 170, "#FFFFFF", 13, "\u9a7e\u9a76\u8231\u4e0e\u9884\u7b97\u5ba1\u6279\u95ed\u73af"),
        t1d=t(68, 190, "#B8ACFE", 12, "\u8fdb\u884c\u4e2d    \u6307\u6d3e\u7ed9\u6211    \u9884\u8ba1 8h"),
        t2=t(68, 236, "#FFFFFF", 13, "\u4ea7\u54c1\u6863\u6848\u5b57\u6bb5\u6838\u5bf9"),
        t2d=t(68, 256, "#B8ACFE", 12, "\u672a\u5f00\u59cb    \u53ef\u70b9\u5f00\u59cb    \u9884\u8ba1 4h"),
        t3=t(68, 302, "#FFFFFF", 13, "\u4f1a\u8bae\u7eaa\u8981\u5f52\u6863\u5230\u6587\u6863\u7a7a\u95f4"),
        t3d=t(68, 322, "#86EFAC", 12, "\u5df2\u5b8c\u6210    \u586b\u5199\u6d88\u8017\u540e\u5173\u95ed"),
        foot=t(76, 423, "#FFFFFF", 12, "\u6307\u6d3e\u3001\u5f00\u59cb\u3001\u5b8c\u6210\uff0c\u56de\u5230\u5de5\u4f5c\u53f0"),
        dots=dots(2),
        rt=t(508, 72, "#0E0B23", 20, "\u65e5\u5e38\u5728\u9636\u6bb5\u91cc\u628a\u4efb\u52a1\u505a\u5b8c", "700"),
        rs=t(508, 104, "#5E626D", 13, "\u6307\u6d3e\u3001\u5f00\u59cb\u3001\u5b8c\u6210\uff0c\u8fdb\u5ea6\u56de\u5230\u5de5\u4f5c\u53f0\u3002"),
        checks=rights([
            (155, "\u5728\u9636\u6bb5\u91cc\u5efa\u4efb\u52a1\u5e76\u6307\u6d3e"),
            (195, "\u5f00\u59cb\u3001\u5b8c\u6210\u65f6\u586b\u5199\u6d88\u8017"),
            (235, "\u5de5\u4f5c\u53f0\u8fdb\u300c\u5f85\u5904\u7406\u4efb\u52a1\u300d"),
            (275, "\u9876\u680f\u5ba1\u6279\u770b\u5f85\u5ba1\u4e8b\u9879"),
            (315, "\u8fc7\u7a0b\u8d44\u6599\u653e\u5230\u6587\u6863\u7a7a\u95f4"),
            (355, "\u770b\u677f\u770b\u903e\u671f\uff0c\u4e0d\u7528\u7ffb\u5217\u8868"),
        ]),
    )


def cn4():
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
        title=t(36, 76, "#FFFFFF", 18, "\u4e09\u6b65\u628a\u9879\u76ee\u63a8\u8d77\u6765", "700"),
        s1=t(96, 136, "#FFFFFF", 15, "\u5efa\u7acb\u6216\u786e\u8ba4\u4ea7\u54c1\u6863\u6848", "700"),
        s1d=t(96, 160, "#D7D0FF", 12, "\u578b\u53f7\u3001\u8bc1\u4ef6\u3001\u6709\u6548\u671f\u5148\u5efa\u6e05\u695a"),
        s2=t(96, 228, "#FFFFFF", 15, "\u521b\u5efa\u9879\u76ee\u5e76\u6dfb\u52a0\u9636\u6bb5", "700"),
        s2d=t(96, 252, "#D7D0FF", 12, "\u5173\u8054\u5df2\u5efa\u6863\u4ea7\u54c1\uff0c\u518d\u62c6\u9636\u6bb5"),
        s3=t(96, 320, "#FFFFFF", 15, "\u5efa\u4efb\u52a1\u63a8\u8fdb\uff0c\u770b\u677f\u770b\u98ce\u9669", "700"),
        s3d=t(96, 344, "#D7D0FF", 12, "\u6307\u6d3e\u3001\u5b8c\u6210\uff0c\u903e\u671f\u4e0e\u5065\u5eb7\u5ea6\u56de\u770b\u677f"),
        foot=t(76, 423, "#FFFFFF", 12, "\u4ece\u5efa\u6863  \u5230  \u5efa\u9879\u76ee  \u5230  \u63a8\u8fdb\u4efb\u52a1"),
        dots=dots(3),
        rt=t(508, 72, "#0E0B23", 20, "\u4e09\u6b65\u5f00\u59cb\uff0c\u9a6c\u4e0a\u80fd\u7528", "700"),
        rs=t(508, 104, "#5E626D", 13, "\u4ece\u5efa\u6863\u5230\u63a8\u8fdb\uff0c\u8fdb\u5ea6\u56de\u5230\u5de5\u4f5c\u53f0\u3002"),
        checks=rights([
            (155, "\u4ea7\u54c1\u7ec4\u5408\uff1a\u5148\u5efa\u6863\u518d\u6302\u9879\u76ee"),
            (195, "\u9879\u76ee\u7ba1\u7406\uff1a\u521b\u5efa\u5e76\u5173\u8054\u4ea7\u54c1"),
            (235, "\u9636\u6bb5\u4efb\u52a1\uff1a\u6307\u6d3e\u5e76\u5b8c\u6210\u4efb\u52a1"),
            (275, "\u5de5\u4f5c\u53f0\uff1a\u9700\u5173\u6ce8\u4e00\u773c\u53ef\u89c1"),
            (315, "\u770b\u677f\u56db\u89c6\u56fe\u770b\u5065\u5eb7\u5ea6"),
            (355, "\u4ea7\u54c1\u5217\u8868\u53ef\u770b\u8bc1\u7167\u4e34\u671f"),
        ]),
    )


# --- English ---
FONT = EN_FONT


def en1():
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
  <rect x="36" y="80" width="408" height="72" rx="14" fill="url(#jxCard)" stroke="#FFFFFF" stroke-opacity=".18"/>
  {hi}{sub}
  <rect x="36" y="164" width="196" height="68" rx="12" fill="#FFFFFF" fill-opacity=".12"/>
  {c1}<text x="52" y="218" fill="#FFFFFF" font-family="{font}" font-size="24" font-weight="700">2</text>
  <rect x="248" y="164" width="196" height="68" rx="12" fill="#FFFFFF" fill-opacity=".12"/>
  {c2}<text x="264" y="218" fill="#FFFFFF" font-family="{font}" font-size="24" font-weight="700">3</text>
  <rect x="36" y="240" width="196" height="68" rx="12" fill="#FFFFFF" fill-opacity=".12"/>
  {c3}<text x="52" y="294" fill="#FFFFFF" font-family="{font}" font-size="24" font-weight="700">1</text>
  <rect x="248" y="240" width="196" height="68" rx="12" fill="#FFFFFF" fill-opacity=".12"/>
  {c4}<text x="264" y="294" fill="#FFFFFF" font-family="{font}" font-size="24" font-weight="700">1</text>
  <rect x="36" y="320" width="408" height="72" rx="12" fill="#FFFFFF" fill-opacity=".1"/>
  <rect x="52" y="336" width="8" height="8" rx="4" fill="#2DD4BF"/>
  {l1}
  <rect x="52" y="356" width="8" height="8" rx="4" fill="#FBBF24"/>
  {l2}
  <rect x="52" y="376" width="8" height="8" rx="4" fill="#FB7185"/>
  {l3}
  <rect x="56" y="404" width="248" height="28" rx="14" fill="#6C5CE7"/>
  {foot}
  {dots}
  {rt}{rs}
  {checks}
""".format(
        font=FONT,
        brand=t(70, 51, "#F4F3FF", 14, "Jenshin Medical PM", "700"),
        ver=t(250, 51, "#D7D0FF", 12, "Workbench  1.0"),
        hi=t(56, 108, "#FFFFFF", 18, "Welcome to Jenshin 1.0", "700"),
        sub=t(56, 134, "#D7D0FF", 13, "Check attention first, then open a project"),
        c1=t(52, 188, "#B8ACFE", 12, "Open todos"),
        c2=t(264, 188, "#B8ACFE", 12, "Open tasks"),
        c3=t(52, 264, "#B8ACFE", 12, "Overdue projects"),
        c4=t(264, 264, "#B8ACFE", 12, "At-risk projects"),
        l1=t(68, 345, "#F4F3FF", 12, "Open task  cockpit and budget loop"),
        l2=t(68, 365, "#F4F3FF", 12, "Overdue  lab management system"),
        l3=t(68, 385, "#F4F3FF", 12, "Cert alert  expires within 90 days"),
        foot=t(76, 423, "#FFFFFF", 12, "See what to do from the workbench"),
        dots=dots(0),
        rt=t(508, 72, "#0E0B23", 20, "Jenshin 1.0 medical PM", "700"),
        rs=t(508, 104, "#5E626D", 13, "Products, projects, stages, and boards in one workspace."),
        checks=rights([
            (147, "Workbench: todos, overdue, and risk"),
            (181, "Products: archives and expiry alerts"),
            (215, "Projects: create and link a product"),
            (249, "Stages: create and drive tasks"),
            (283, "Boards: health and overdue work"),
            (317, "Docs: file project materials"),
            (351, "Org: see teammates and depts"),
        ]),
    )


def en2():
    return """
  <rect width="800" height="460" fill="#FCFDFE"/>
  <defs>
    <linearGradient id="jxBg2" x1="0" y1="0" x2="480" y2="460" gradientUnits="userSpaceOnUse">
      <stop stop-color="#171338"/><stop offset="1" stop-color="#4A39B4"/>
    </linearGradient>
  </defs>
  <rect width="480" height="460" fill="url(#jxBg2)"/>
  <circle cx="40" cy="60" r="80" fill="#2DD4BF" fill-opacity=".12"/>
  {kicker}{title}
  <rect x="36" y="100" width="408" height="196" rx="14" fill="#FFFFFF" fill-opacity=".1"/>
  {list}{row1}
  <rect x="320" y="140" width="52" height="18" rx="9" fill="#FBBF24"/>
  {soon}{row2}{meta}{hint}{note}
  <rect x="36" y="308" width="196" height="72" rx="14" fill="#FFFFFF" fill-opacity=".12"/>
  {p1}{p1d}
  <rect x="248" y="308" width="196" height="72" rx="14" fill="#FFFFFF" fill-opacity=".12"/>
  {p2}{p2d}
  <rect x="56" y="404" width="268" height="28" rx="14" fill="#6C5CE7"/>
  {foot}
  {dots}
  {rt}{rs}
  {checks}
""".format(
        kicker=t(36, 48, "#D7D0FF", 13, "Products"),
        title=t(36, 76, "#FFFFFF", 18, "Archive first, then a project", "700"),
        list=t(52, 126, "#B8ACFE", 12, "Product list"),
        row1=t(52, 154, "#FFFFFF", 13, "JX-WS80    Class II    Cert 2026"),
        soon=t(328, 153, "#0E0B23", 11, "Soon", "700"),
        row2=t(52, 182, "#FFFFFF", 13, "JX-BB18    Class III    Cert 2025"),
        meta=t(52, 210, "#D7D0FF", 12, "Model \u00b7 class \u00b7 cert No. \u00b7 expiry"),
        hint=t(52, 236, "#D7D0FF", 12, "Expires in 90 days: orange \u201cSoon\u201d"),
        note=t(52, 270, "#B8ACFE", 12, "Clear files keep certs and progress aligned"),
        p1=t(52, 336, "#2DD4BF", 12, "Create project"),
        p1d=t(52, 362, "#FFFFFF", 13, "Product  /  Project type"),
        p2=t(264, 336, "#9384FB", 12, "Add a stage"),
        p2d=t(264, 362, "#FFFFFF", 13, "Short  /  Long  /  Ops"),
        foot=t(76, 423, "#FFFFFF", 12, "Clear archives keep projects on track"),
        dots=dots(1),
        rt=t(508, 72, "#0E0B23", 18, "Archive first, then project and stage", "700"),
        rs=t(508, 104, "#5E626D", 13, "Clear product files keep certs and progress aligned."),
        checks=rights([
            (155, "File: model, class, cert, expiry"),
            (195, "Certs due in 90 days show \u201cSoon\u201d"),
            (235, "Create: product or project type"),
            (275, "Link an archived product when you can"),
            (315, "Add stages: short / long / ops"),
            (355, "Stage Tasks lists every stage"),
        ]),
    )


def en3():
    return """
  <rect width="800" height="460" fill="#FCFDFE"/>
  <defs>
    <linearGradient id="jxBg3" x1="0" y1="0" x2="480" y2="460" gradientUnits="userSpaceOnUse">
      <stop stop-color="#1E1850"/><stop offset="1" stop-color="#322877"/>
    </linearGradient>
  </defs>
  <rect width="480" height="460" fill="url(#jxBg3)"/>
  {kicker}{title}
  <rect x="36" y="100" width="408" height="272" rx="14" fill="#FFFFFF" fill-opacity=".1"/>
  {stage}
  <rect x="340" y="110" width="80" height="24" rx="12" fill="#6C5CE7"/>
  {newbtn}
  <rect x="52" y="148" width="376" height="56" rx="10" fill="#FFFFFF" fill-opacity=".08"/>
  {t1}{t1d}
  <rect x="52" y="214" width="376" height="56" rx="10" fill="#FFFFFF" fill-opacity=".08"/>
  {t2}{t2d}
  <rect x="52" y="280" width="376" height="56" rx="10" fill="#FFFFFF" fill-opacity=".08"/>
  {t3}{t3d}
  <rect x="56" y="404" width="268" height="28" rx="14" fill="#6C5CE7"/>
  {foot}
  {dots}
  {rt}{rs}
  {checks}
""".format(
        kicker=t(36, 48, "#D7D0FF", 13, "Stage tasks"),
        title=t(36, 76, "#FFFFFF", 18, "Finish work inside the stage", "700"),
        stage=t(52, 128, "#B8ACFE", 12, "Stage V1.0"),
        newbtn=t(352, 127, "#FFFFFF", 12, "New task"),
        t1=t(68, 170, "#FFFFFF", 13, "Cockpit and budget approval loop"),
        t1d=t(68, 190, "#B8ACFE", 12, "Doing    Assigned to me    8h est."),
        t2=t(68, 236, "#FFFFFF", 13, "Check product archive fields"),
        t2d=t(68, 256, "#B8ACFE", 12, "Wait    Start when ready    4h est."),
        t3=t(68, 302, "#FFFFFF", 13, "File meeting notes in Docs"),
        t3d=t(68, 322, "#86EFAC", 12, "Done    Log effort, then close"),
        foot=t(76, 423, "#FFFFFF", 12, "Assign, start, finish; back to workbench"),
        dots=dots(2),
        rt=t(508, 72, "#0E0B23", 20, "Finish work inside the stage", "700"),
        rs=t(508, 104, "#5E626D", 13, "Assign, start, complete; progress returns here."),
        checks=rights([
            (155, "Create and assign tasks in a stage"),
            (195, "Log effort when you start or finish"),
            (235, "Workbench opens tasks assigned to you"),
            (275, "Top bar Review shows pending items"),
            (315, "Put working files in Docs"),
            (355, "Use the board for overdue, not lists"),
        ]),
    )


def en4():
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
  <rect x="56" y="404" width="280" height="28" rx="14" fill="#6C5CE7"/>
  {foot}
  {dots}
  {rt}{rs}
  {checks}
""".format(
        font=FONT,
        kicker=t(36, 48, "#D7D0FF", 13, "Start"),
        title=t(36, 76, "#FFFFFF", 18, "Three steps to get a project moving", "700"),
        s1=t(96, 136, "#FFFFFF", 15, "Create or confirm the product file", "700"),
        s1d=t(96, 160, "#D7D0FF", 12, "Model, certificate, and expiry first"),
        s2=t(96, 228, "#FFFFFF", 15, "Create a project and add stages", "700"),
        s2d=t(96, 252, "#D7D0FF", 12, "Link the archived product, then split stages"),
        s3=t(96, 320, "#FFFFFF", 15, "Drive tasks, watch risk on the board", "700"),
        s3d=t(96, 344, "#D7D0FF", 12, "Assign and finish; overdue and health return"),
        foot=t(76, 423, "#FFFFFF", 12, "Archive  \u2192  project  \u2192  drive tasks"),
        dots=dots(3),
        rt=t(508, 72, "#0E0B23", 20, "Three steps, ready to use", "700"),
        rs=t(508, 104, "#5E626D", 13, "From archive to progress, back on the workbench."),
        checks=rights([
            (155, "Products: archive, then link a project"),
            (195, "Projects: create and link a product"),
            (235, "Stages: assign and finish tasks"),
            (275, "Workbench: attention at a glance"),
            (315, "Four board views for health"),
            (355, "Product list shows certs due soon"),
        ]),
    )


if __name__ == "__main__":
    FONT = CN_FONT
    write("cn_guide1_1_1.svg", cn1())
    write("cn_guide2_1_1.svg", cn2())
    write("cn_guide3_1_1.svg", cn3())
    write("cn_guide4_1_1.svg", cn4())
    FONT = EN_FONT
    write("en_guide1_1_1.svg", en1())
    write("en_guide2_1_1.svg", en2())
    write("en_guide3_1_1.svg", en3())
    write("en_guide4_1_1.svg", en4())
