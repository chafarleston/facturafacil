:root {
    --primary: {{ $web['primary_color'] }};
    --secondary: {{ $web['secondary_color'] }};
    --bg: #f4f5f8;
    --surface: #ffffff;
    --surface-2: #f7f8fa;
    --text: #1f2430;
    --muted: #7b8190;
    --border: #e8eaf0;
    --radius: 22px;
    --radius-sm: 12px;
    --shadow: 0 18px 50px rgba(20, 25, 40, .10);
    --shadow-sm: 0 8px 24px rgba(20, 25, 40, .07);
    --banner-bg: linear-gradient(135deg, var(--primary), var(--secondary));
    --banner-color: #fff;
    --banner-border: 0;
    --banner-letter: 3px;
    --banner-shadow: 0 8px 20px rgba(0, 0, 0, .12);
    --card-border: 1px solid var(--border);
    --head-border: 1px dashed var(--border);
    --grand-color: var(--primary);
    --button-bg: var(--primary);
    --button-color: #fff;
    --brand-transform: none;
    --body-image: radial-gradient(circle at 15% 0%, rgba(0, 0, 0, .035), transparent 42%);
    --font: '{{ $web['font'] }}', sans-serif;
}

body[data-theme="elegant"] {
    --bg: #f8f5ef;
    --surface: #ffffff;
    --surface-2: #faf7f1;
    --text: #241f19;
    --muted: #7b7164;
    --border: #e7dfd2;
    --radius: 3px;
    --radius-sm: 2px;
    --shadow: 0 14px 34px rgba(60, 45, 20, .07);
    --shadow-sm: 0 8px 20px rgba(60, 45, 20, .05);
    --banner-bg: transparent;
    --banner-color: var(--primary);
    --banner-border: 1px solid #d9cbb7;
    --banner-letter: 6px;
    --banner-shadow: none;
    --card-border: 1px solid #d9cbb7;
    --grand-color: var(--primary);
    --button-bg: #241f19;
    --brand-transform: uppercase;
    --body-image: none;
}

body[data-theme="minimal"] {
    --bg: #f7f7f7;
    --surface: #ffffff;
    --surface-2: #fafafa;
    --text: #111111;
    --muted: #666666;
    --border: #e5e5e5;
    --radius: 0;
    --radius-sm: 0;
    --shadow: none;
    --shadow-sm: none;
    --banner-bg: #111111;
    --banner-color: #ffffff;
    --banner-border: 0;
    --banner-letter: 4px;
    --banner-shadow: none;
    --card-border: 1px solid #dddddd;
    --head-border: 1px solid #eeeeee;
    --grand-color: #111111;
    --button-bg: #111111;
    --brand-transform: none;
    --body-image: none;
}

body[data-theme="classic"] {
    --bg: #f3ede2;
    --surface: #ffffff;
    --surface-2: #fbf7f0;
    --text: #2f2a22;
    --muted: #7b6f5e;
    --border: #d8cdbc;
    --radius: 0;
    --radius-sm: 0;
    --shadow: 0 12px 28px rgba(60, 45, 20, .08);
    --shadow-sm: 0 6px 18px rgba(60, 45, 20, .06);
    --banner-bg: var(--secondary);
    --banner-color: #ffffff;
    --banner-border: 0;
    --banner-letter: 4px;
    --banner-shadow: none;
    --card-border: 1px solid #d8cdbc;
    --head-border: 1px solid #e3d9c9;
    --grand-color: var(--primary);
    --button-bg: #2f2a22;
    --brand-transform: uppercase;
    --body-image: none;
}

body[data-theme="dark"] {
    --bg: #0b0d12;
    --surface: #151923;
    --surface-2: #11151e;
    --text: #e9e9f2;
    --muted: #9aa3b2;
    --border: #242a38;
    --radius: 18px;
    --radius-sm: 12px;
    --shadow: 0 22px 60px rgba(0, 0, 0, .45);
    --shadow-sm: 0 10px 28px rgba(0, 0, 0, .28);
    --banner-bg: linear-gradient(135deg, var(--primary), var(--secondary));
    --banner-color: #ffffff;
    --banner-border: 0;
    --banner-letter: 3px;
    --banner-shadow: 0 8px 22px rgba(0, 0, 0, .35);
    --card-border: 1px solid #242a38;
    --head-border: 1px dashed #2a3142;
    --grand-color: #ffffff;
    --button-bg: linear-gradient(135deg, var(--primary), var(--secondary));
    --brand-transform: none;
    --body-image: radial-gradient(circle at 15% 0%, rgba(255, 255, 255, .05), transparent 42%);
    color-scheme: dark;
}

body[data-theme="tropical"] {
    --bg: #fffaf2;
    --surface: #ffffff;
    --surface-2: #fff4e6;
    --text: #12332e;
    --muted: #6b7f7a;
    --border: #f0e2cc;
    --radius: 26px;
    --radius-sm: 16px;
    --shadow: 0 18px 44px rgba(255, 122, 89, .13);
    --shadow-sm: 0 8px 22px rgba(255, 122, 89, .09);
    --banner-bg: linear-gradient(135deg, var(--primary), var(--secondary));
    --banner-color: #ffffff;
    --banner-border: 0;
    --banner-letter: 2px;
    --banner-shadow: 0 8px 22px rgba(255, 122, 89, .20);
    --card-border: 1px solid #f0e2cc;
    --head-border: 1px dashed #eadbc5;
    --grand-color: var(--primary);
    --button-bg: linear-gradient(135deg, var(--primary), var(--secondary));
    --brand-transform: none;
    --body-image: radial-gradient(circle at 90% 100%, rgba(0, 184, 148, .12), transparent 40%);
}

body[data-theme="chifa"] {
    --bg: #fff8ee;
    --surface: #fffdf8;
    --surface-2: #fff0d8;
    --text: #3f1710;
    --muted: #8a5a45;
    --border: #e6c9a0;
    --radius: 8px;
    --radius-sm: 6px;
    --shadow: 0 16px 40px rgba(139, 0, 0, .12);
    --shadow-sm: 0 8px 22px rgba(139, 0, 0, .08);
    --banner-bg: linear-gradient(135deg, var(--primary), var(--secondary));
    --banner-color: #fffdf8;
    --banner-border: 0;
    --banner-letter: 3px;
    --banner-shadow: 0 8px 22px rgba(139, 0, 0, .18);
    --card-border: 1px solid #e6c9a0;
    --head-border: 1px dashed #d7b88a;
    --grand-color: var(--primary);
    --button-bg: var(--primary);
    --button-color: #fffdf8;
    --brand-transform: none;
    --body-image:
        radial-gradient(circle at 12% 8%, rgba(212, 175, 55, .13) 0 2px, transparent 3px),
        radial-gradient(circle at 88% 18%, rgba(158, 27, 27, .08) 0 2px, transparent 3px);
}
