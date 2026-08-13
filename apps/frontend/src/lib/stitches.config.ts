/**
 * Stitches 디자인 토큰 시스템
 * 농업 테마 색상, 타이포그래피, 스페이싱
 * 다크모드 + 고대비 모드(야외 스마트폰) 지원
 */

import { createStitches } from '@stitches/react'

export const {
  styled,
  css,
  globalCss,
  keyframes,
  getCssText,
  theme,
  createTheme,
  config,
} = createStitches({
  theme: {
    colors: {
      // ── 배경 ──────────────────────────────────────────────────
      bgBase: 'hsl(100, 20%, 96%)',        // 자연스러운 연두빛 흰 배경
      bgSurface: 'hsl(0, 0%, 100%)',
      bgCard: 'hsl(0, 0%, 100%)',
      bgMuted: 'hsl(100, 15%, 93%)',

      // ── 메인 컬러 (농업 그린) ──────────────────────────────────
      primary50:  'hsl(140, 60%, 95%)',
      primary100: 'hsl(140, 55%, 88%)',
      primary200: 'hsl(140, 50%, 75%)',
      primary300: 'hsl(140, 45%, 60%)',
      primary400: 'hsl(140, 45%, 48%)',
      primary500: 'hsl(140, 50%, 38%)',  // 메인 브랜드 컬러
      primary600: 'hsl(140, 55%, 30%)',
      primary700: 'hsl(140, 60%, 22%)',
      primary: '$primary500',
      primaryHover: '$primary600',
      primaryLight: '$primary50',

      // ── 수확 골드 (액센트) ────────────────────────────────────
      accent50:  'hsl(42, 100%, 95%)',
      accent500: 'hsl(42, 95%, 50%)',
      accent600: 'hsl(38, 90%, 42%)',
      accent: '$accent500',
      accentHover: '$accent600',
      accentLight: '$accent50',

      // ── 시맨틱 컬러 ───────────────────────────────────────────
      success: 'hsl(140, 50%, 38%)',
      successLight: 'hsl(140, 60%, 94%)',
      warning: 'hsl(38, 90%, 48%)',
      warningLight: 'hsl(42, 100%, 94%)',
      danger: 'hsl(0, 72%, 50%)',
      dangerLight: 'hsl(0, 80%, 95%)',
      info: 'hsl(210, 90%, 45%)',
      infoLight: 'hsl(210, 100%, 95%)',

      // ── 텍스트 ────────────────────────────────────────────────
      textPrimary: 'hsl(140, 25%, 12%)',
      textSecondary: 'hsl(140, 15%, 35%)',
      textMuted: 'hsl(140, 10%, 55%)',
      textInverse: 'hsl(0, 0%, 100%)',

      // ── 보더 / 구분선 ─────────────────────────────────────────
      border: 'hsl(130, 25%, 88%)',
      borderStrong: 'hsl(130, 20%, 75%)',

      // ── 센서 상태 컬러 (대시보드 카드) ───────────────────────
      statusNormal: 'hsl(140, 50%, 38%)',   // 정상
      statusWarning: 'hsl(38, 90%, 48%)',   // 경고
      statusCritical: 'hsl(0, 72%, 50%)',   // 위험
      statusOffline: 'hsl(0, 0%, 60%)',     // 오프라인
    },

    space: {
      1: '4px',
      2: '8px',
      3: '12px',
      4: '16px',
      5: '20px',
      6: '24px',
      7: '32px',
      8: '40px',
      9: '48px',
      10: '64px',
      11: '80px',
      12: '96px',
    },

    fontSizes: {
      xs: '11px',
      sm: '13px',
      md: '15px',
      lg: '18px',
      xl: '22px',
      '2xl': '28px',
      '3xl': '36px',
      '4xl': '48px',
    },

    fonts: {
      body: '"Pretendard", "Noto Sans KR", -apple-system, sans-serif',
      heading: '"Pretendard", "Noto Sans KR", sans-serif',
      mono: '"JetBrains Mono", "Fira Code", monospace',
    },

    fontWeights: {
      regular: '400',
      medium: '500',
      semibold: '600',
      bold: '700',
    },

    lineHeights: {
      tight: '1.25',
      normal: '1.5',
      relaxed: '1.75',
    },

    letterSpacings: {
      tight: '-0.02em',
      normal: '0',
      wide: '0.04em',
      widest: '0.08em',
    },

    radii: {
      xs: '4px',
      sm: '6px',
      md: '10px',
      lg: '14px',
      xl: '20px',
      '2xl': '28px',
      full: '9999px',
    },

    shadows: {
      sm: '0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04)',
      md: '0 4px 6px rgba(0,0,0,0.05), 0 2px 4px rgba(0,0,0,0.04)',
      lg: '0 10px 15px rgba(0,0,0,0.05), 0 4px 6px rgba(0,0,0,0.03)',
      xl: '0 20px 25px rgba(0,0,0,0.06), 0 10px 10px rgba(0,0,0,0.02)',
    },

    transitions: {
      fast: '120ms ease',
      normal: '200ms ease',
      slow: '350ms ease',
    },

    zIndices: {
      dropdown: '100',
      sticky: '200',
      overlay: '300',
      modal: '400',
      toast: '500',
    },
  },

  media: {
    mobile: '(max-width: 480px)',
    tablet: '(max-width: 768px)',
    desktop: '(min-width: 1024px)',
    wide: '(min-width: 1440px)',
  },

  utils: {
    // 단축 유틸리티
    p: (value: string | number) => ({ padding: value }),
    pt: (value: string | number) => ({ paddingTop: value }),
    pr: (value: string | number) => ({ paddingRight: value }),
    pb: (value: string | number) => ({ paddingBottom: value }),
    pl: (value: string | number) => ({ paddingLeft: value }),
    px: (value: string | number) => ({ paddingLeft: value, paddingRight: value }),
    py: (value: string | number) => ({ paddingTop: value, paddingBottom: value }),

    m: (value: string | number) => ({ margin: value }),
    mx: (value: string | number) => ({ marginLeft: value, marginRight: value }),
    my: (value: string | number) => ({ marginTop: value, marginBottom: value }),

    size: (value: string | number) => ({ width: value, height: value }),
    minSize: (value: string | number) => ({ minWidth: value, minHeight: value }),

    // Flexbox 단축
    flexCenter: (_: boolean) => ({
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
    }),
    flexBetween: (_: boolean) => ({
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'space-between',
    }),
    flexColumn: (_: boolean) => ({
      display: 'flex',
      flexDirection: 'column',
    }),
  },
})

// ── 다크 테마 ───────────────────────────────────────────────────────────
export const darkTheme = createTheme('dark-theme', {
  colors: {
    bgBase: 'hsl(140, 15%, 8%)',
    bgSurface: 'hsl(140, 12%, 12%)',
    bgCard: 'hsl(140, 12%, 15%)',
    bgMuted: 'hsl(140, 10%, 18%)',

    textPrimary: 'hsl(140, 20%, 94%)',
    textSecondary: 'hsl(140, 10%, 70%)',
    textMuted: 'hsl(140, 8%, 50%)',

    border: 'hsl(140, 15%, 22%)',
    borderStrong: 'hsl(140, 12%, 32%)',
  },
})

// ── 고대비 테마 (야외 스마트폰용) ──────────────────────────────────────
export const highContrastTheme = createTheme('high-contrast-theme', {
  colors: {
    bgBase: 'hsl(0, 0%, 100%)',
    bgCard: 'hsl(0, 0%, 100%)',

    primary: 'hsl(140, 60%, 25%)',  // 더 진한 그린
    textPrimary: 'hsl(0, 0%, 5%)',  // 거의 검정
    textSecondary: 'hsl(0, 0%, 20%)',

    border: 'hsl(0, 0%, 40%)',      // 진한 테두리

    // 상태 색상도 더 진하게
    statusNormal: 'hsl(140, 70%, 28%)',
    statusWarning: 'hsl(38, 100%, 38%)',
    statusCritical: 'hsl(0, 85%, 42%)',
  },
  fontSizes: {
    xs: '13px',  // 야외 가독성을 위해 전체적으로 1단계 업
    sm: '15px',
    md: '17px',
    lg: '20px',
    xl: '24px',
  },
})

// ── 글로벌 CSS ─────────────────────────────────────────────────────────
export const globalStyles = globalCss({
  '*': {
    boxSizing: 'border-box',
    margin: 0,
    padding: 0,
  },

  'html, body': {
    fontFamily: '$body',
    backgroundColor: '$bgBase',
    color: '$textPrimary',
    lineHeight: '$normal',
    fontSize: '16px',
    WebkitFontSmoothing: 'antialiased',
    MozOsxFontSmoothing: 'grayscale',
  },

  ':root': {
    scrollBehavior: 'smooth',
  },

  // 스크롤바 스타일
  '::-webkit-scrollbar': { width: '6px', height: '6px' },
  '::-webkit-scrollbar-track': { background: 'transparent' },
  '::-webkit-scrollbar-thumb': {
    background: '$borderStrong',
    borderRadius: '$full',
  },

  // 포커스 스타일 (접근성)
  ':focus-visible': {
    outline: '2px solid $primary',
    outlineOffset: '2px',
  },

  // Recharts 텍스트 색상 보정
  '.recharts-text': {
    fill: '$textSecondary',
    fontSize: '$sm',
  },
})
