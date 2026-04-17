# Design System Apple Brasil

> Documentação completa do sistema de design baseado em apple.com/br

---

## 📋 Índice

1. [Princípios de Design](#princípios-de-design)
2. [Cores](#cores)
3. [Tipografia](#tipografia)
4. [Espaçamento](#espaçamento)
5. [Grid e Layout](#grid-e-layout)
6. [Componentes](#componentes)
7. [Animações e Transições](#animações-e-transições)
8. [Iconografia](#iconografia)
9. [Imagens e Mídia](#imagens-e-mídia)
10. [Acessibilidade](#acessibilidade)

---

## 🎯 Princípios de Design

### Minimalismo Funcional
- **Clareza acima de tudo**: Cada elemento serve um propósito claro
- **Espaço em branco**: Uso generoso de espaço negativo para respiração visual
- **Hierarquia visual forte**: Diferenciação clara entre elementos primários e secundários

### Focado no Produto
- **Fotografia de produto premium**: Imagens de alta qualidade em destaque
- **Tipografia como suporte**: Texto complementa, não compete com o produto
- **Call-to-actions sutis**: Links discretos mas efetivos

### Simplicidade e Elegância
- **Menos é mais**: Remover elementos desnecessários
- **Consistência**: Padrões visuais repetidos em toda a experiência
- **Atenção aos detalhes**: Refinamento em cada pixel

---

## 🎨 Cores

### Cores Primárias

```css
/* Background */
--color-bg-primary: #FFFFFF;        /* Branco puro */
--color-bg-secondary: #F5F5F7;      /* Cinza claríssimo */
--color-bg-dark: #000000;           /* Preto profundo */
--color-bg-elevated: #FBFBFD;       /* Branco elevado */

/* Texto */
--color-text-primary: #1D1D1F;      /* Cinza quase preto */
--color-text-secondary: #6E6E73;    /* Cinza médio */
--color-text-tertiary: #86868B;     /* Cinza claro */
--color-text-inverse: #F5F5F7;      /* Texto em fundo escuro */

/* Links e Ações */
--color-link: #0071E3;              /* Azul Apple */
--color-link-hover: #0077ED;        /* Azul hover */
--color-link-active: #006EDB;       /* Azul ativo */
```

### Cores de Produto

```css
/* Cores características dos produtos */
--color-product-space-black: #1D1D1F;
--color-product-silver: #E3E4E5;
--color-product-gold: #FAE7D4;
--color-product-midnight: #2D3640;
--color-product-starlight: #F0E4D3;
--color-product-blue: #276787;
--color-product-purple: #9F86C0;
--color-product-pink: #E8D2D2;
--color-product-yellow: #F9E7C9;
--color-product-green: #ACD5B4;
--color-product-red: #C94843;
```

### Cores de Sistema

```css
/* Feedbacks e Estados */
--color-success: #34C759;
--color-warning: #FF9500;
--color-error: #FF3B30;
--color-info: #007AFF;

/* Bordas e Divisores */
--color-border: #D2D2D7;
--color-border-subtle: #E8E8ED;
--color-divider: rgba(0, 0, 0, 0.08);
```

---

## ✍️ Tipografia

### Família Tipográfica

```css
/* Fonte principal - SF Pro Display (headlines) */
--font-family-display: "SF Pro Display", -apple-system, BlinkMacSystemFont, sans-serif;

/* Fonte secundária - SF Pro Text (body) */
--font-family-text: "SF Pro Text", -apple-system, BlinkMacSystemFont, sans-serif;

/* Fonte monoespaçada */
--font-family-mono: "SF Mono", SFMono-Regular, Consolas, monospace;
```

### Escalas de Tamanho

#### Headlines (Display)

```css
/* Ultra Large - Hero sections */
--font-size-display-xl: 96px;
--line-height-display-xl: 1.05;
--letter-spacing-display-xl: -0.015em;
--font-weight-display-xl: 600;

/* Large - Section heroes */
--font-size-display-lg: 80px;
--line-height-display-lg: 1.05;
--letter-spacing-display-lg: -0.015em;
--font-weight-display-lg: 600;

/* Medium - Product headers */
--font-size-display-md: 64px;
--line-height-display-md: 1.0625;
--letter-spacing-display-md: -0.009em;
--font-weight-display-md: 600;

/* Small - Subsection headers */
--font-size-display-sm: 48px;
--line-height-display-sm: 1.0834;
--letter-spacing-display-sm: -0.003em;
--font-weight-display-sm: 600;

/* XSmall - Card headers */
--font-size-display-xs: 40px;
--line-height-display-xs: 1.1;
--letter-spacing-display-xs: 0em;
--font-weight-display-xs: 600;
```

#### Headings (Text)

```css
/* H1 */
--font-size-h1: 32px;
--line-height-h1: 1.125;
--letter-spacing-h1: 0.004em;
--font-weight-h1: 600;

/* H2 */
--font-size-h2: 28px;
--line-height-h2: 1.1429;
--letter-spacing-h2: 0.007em;
--font-weight-h2: 600;

/* H3 */
--font-size-h3: 24px;
--line-height-h3: 1.1667;
--letter-spacing-h3: 0.009em;
--font-weight-h3: 600;

/* H4 */
--font-size-h4: 21px;
--line-height-h4: 1.1905;
--letter-spacing-h4: 0.011em;
--font-weight-h4: 600;

/* H5 */
--font-size-h5: 19px;
--line-height-h5: 1.2106;
--letter-spacing-h5: 0.012em;
--font-weight-h5: 600;
```

#### Body Text

```css
/* Lead - Introdução de seção */
--font-size-lead: 21px;
--line-height-lead: 1.381;
--letter-spacing-lead: 0.011em;
--font-weight-lead: 400;

/* Body Large */
--font-size-body-lg: 19px;
--line-height-body-lg: 1.4211;
--letter-spacing-body-lg: 0.012em;
--font-weight-body-lg: 400;

/* Body Regular */
--font-size-body: 17px;
--line-height-body: 1.4706;
--letter-spacing-body: -0.022em;
--font-weight-body: 400;

/* Body Small */
--font-size-body-sm: 14px;
--line-height-body-sm: 1.4286;
--letter-spacing-body-sm: -0.016em;
--font-weight-body-sm: 400;

/* Caption */
--font-size-caption: 12px;
--line-height-caption: 1.3334;
--letter-spacing-caption: 0em;
--font-weight-caption: 400;
```

### Pesos de Fonte

```css
--font-weight-light: 300;
--font-weight-regular: 400;
--font-weight-medium: 500;
--font-weight-semibold: 600;
--font-weight-bold: 700;
```

---

## 📏 Espaçamento

### Sistema de Espaçamento Base

```css
/* Base de 4px */
--space-1: 4px;    /* 0.25rem */
--space-2: 8px;    /* 0.5rem */
--space-3: 12px;   /* 0.75rem */
--space-4: 16px;   /* 1rem */
--space-5: 20px;   /* 1.25rem */
--space-6: 24px;   /* 1.5rem */
--space-8: 32px;   /* 2rem */
--space-10: 40px;  /* 2.5rem */
--space-12: 48px;  /* 3rem */
--space-16: 64px;  /* 4rem */
--space-20: 80px;  /* 5rem */
--space-24: 96px;  /* 6rem */
--space-32: 128px; /* 8rem */
--space-40: 160px; /* 10rem */
--space-48: 192px; /* 12rem */
```

### Espaçamento Semântico

```css
/* Componentes */
--space-component-xs: var(--space-2);   /* 8px */
--space-component-sm: var(--space-3);   /* 12px */
--space-component-md: var(--space-4);   /* 16px */
--space-component-lg: var(--space-6);   /* 24px */
--space-component-xl: var(--space-8);   /* 32px */

/* Seções */
--space-section-xs: var(--space-12);    /* 48px */
--space-section-sm: var(--space-16);    /* 64px */
--space-section-md: var(--space-24);    /* 96px */
--space-section-lg: var(--space-32);    /* 128px */
--space-section-xl: var(--space-40);    /* 160px */

/* Container padding */
--space-container-mobile: var(--space-4);   /* 16px */
--space-container-tablet: var(--space-6);   /* 24px */
--space-container-desktop: var(--space-10); /* 40px */
```

---

## 📐 Grid e Layout

### Container

```css
/* Max widths */
--container-sm: 692px;
--container-md: 980px;
--container-lg: 1024px;
--container-xl: 1440px;

/* Container com padding lateral */
.container {
  width: 100%;
  max-width: var(--container-lg);
  margin: 0 auto;
  padding-left: var(--space-container-mobile);
  padding-right: var(--space-container-mobile);
}

@media (min-width: 768px) {
  .container {
    padding-left: var(--space-container-tablet);
    padding-right: var(--space-container-tablet);
  }
}

@media (min-width: 1024px) {
  .container {
    padding-left: var(--space-container-desktop);
    padding-right: var(--space-container-desktop);
  }
}
```

### Grid System

```css
/* Grid 12 colunas */
.grid {
  display: grid;
  grid-template-columns: repeat(12, 1fr);
  gap: var(--space-6);
}

/* Grid responsivo para produtos */
.product-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-8);
}

@media (min-width: 768px) {
  .product-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (min-width: 1024px) {
  .product-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}
```

### Breakpoints

```css
/* Mobile First */
--breakpoint-xs: 0;
--breakpoint-sm: 640px;   /* Tablet pequeno */
--breakpoint-md: 768px;   /* Tablet */
--breakpoint-lg: 1024px;  /* Desktop */
--breakpoint-xl: 1280px;  /* Desktop grande */
--breakpoint-2xl: 1536px; /* Desktop extra grande */
```

---

## 🧩 Componentes

### Navigation Bar

```css
.navbar {
  background-color: rgba(0, 0, 0, 0.8);
  backdrop-filter: saturate(180%) blur(20px);
  height: 44px;
  position: sticky;
  top: 0;
  z-index: 1000;
}

.navbar-menu {
  display: flex;
  justify-content: space-between;
  align-items: center;
  height: 100%;
  padding: 0 var(--space-4);
}

.navbar-link {
  color: var(--color-text-inverse);
  font-size: 12px;
  font-weight: var(--font-weight-regular);
  text-decoration: none;
  padding: 0 var(--space-2);
  transition: opacity 0.3s ease;
}

.navbar-link:hover {
  opacity: 0.7;
}
```

### Hero Section

```html
<!-- Hero com imagem de produto -->
<section class="hero">
  <div class="hero-content">
    <h1 class="hero-title">MacBook Neo</h1>
    <p class="hero-subtitle">Um Mac incrível. Uma escolha inteligente.</p>
    <div class="hero-actions">
      <a href="#" class="link-primary">Saiba mais</a>
      <a href="#" class="link-secondary">Ver preços</a>
    </div>
  </div>
  <div class="hero-media">
    <img src="product.jpg" alt="MacBook Neo">
  </div>
</section>
```

```css
.hero {
  min-height: 90vh;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  text-align: center;
  padding: var(--space-section-lg) var(--space-4);
  background: linear-gradient(to bottom, #FBFBFD, #F5F5F7);
}

.hero-title {
  font-size: var(--font-size-display-lg);
  line-height: var(--line-height-display-lg);
  font-weight: var(--font-weight-display-lg);
  letter-spacing: var(--letter-spacing-display-lg);
  color: var(--color-text-primary);
  margin-bottom: var(--space-3);
}

.hero-subtitle {
  font-size: var(--font-size-h2);
  line-height: var(--line-height-h2);
  color: var(--color-text-secondary);
  margin-bottom: var(--space-8);
}

.hero-actions {
  display: flex;
  gap: var(--space-6);
  flex-wrap: wrap;
  justify-content: center;
}
```

### Product Card

```html
<div class="product-card">
  <div class="product-card-media">
    <img src="product.jpg" alt="iPhone 17e">
  </div>
  <div class="product-card-content">
    <h3 class="product-card-title">iPhone 17e</h3>
    <p class="product-card-description">Equipado. Para valer.</p>
    <div class="product-card-actions">
      <a href="#" class="link-primary">Saiba mais</a>
      <a href="#" class="link-secondary">Comprar</a>
    </div>
  </div>
</div>
```

```css
.product-card {
  background-color: var(--color-bg-primary);
  border-radius: 18px;
  overflow: hidden;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.product-card:hover {
  transform: scale(1.02);
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
}

.product-card-media {
  aspect-ratio: 16 / 9;
  overflow: hidden;
  background: linear-gradient(135deg, #F5F5F7 0%, #E8E8ED 100%);
}

.product-card-media img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.product-card-content {
  padding: var(--space-6);
  text-align: center;
}

.product-card-title {
  font-size: var(--font-size-h3);
  font-weight: var(--font-weight-h3);
  color: var(--color-text-primary);
  margin-bottom: var(--space-2);
}

.product-card-description {
  font-size: var(--font-size-body);
  color: var(--color-text-secondary);
  margin-bottom: var(--space-5);
}

.product-card-actions {
  display: flex;
  gap: var(--space-4);
  justify-content: center;
}
```

### Links e Botões

```css
/* Link primário - Azul Apple */
.link-primary {
  color: var(--color-link);
  font-size: 17px;
  text-decoration: none;
  transition: color 0.2s ease;
  position: relative;
}

.link-primary::after {
  content: '>';
  margin-left: 0.3em;
  transition: transform 0.2s ease;
  display: inline-block;
}

.link-primary:hover {
  color: var(--color-link-hover);
}

.link-primary:hover::after {
  transform: translateX(4px);
}

/* Link secundário - Cinza */
.link-secondary {
  color: var(--color-text-secondary);
  font-size: 17px;
  text-decoration: none;
  transition: color 0.2s ease;
}

.link-secondary:hover {
  color: var(--color-text-primary);
}

/* Botão primário */
.button-primary {
  background-color: var(--color-link);
  color: white;
  padding: var(--space-3) var(--space-6);
  border-radius: 980px;
  font-size: 17px;
  font-weight: var(--font-weight-regular);
  text-decoration: none;
  border: none;
  cursor: pointer;
  transition: background-color 0.2s ease;
  display: inline-block;
}

.button-primary:hover {
  background-color: var(--color-link-hover);
}

/* Botão secundário */
.button-secondary {
  background-color: transparent;
  color: var(--color-link);
  padding: var(--space-3) var(--space-6);
  border-radius: 980px;
  font-size: 17px;
  font-weight: var(--font-weight-regular);
  text-decoration: none;
  border: 1px solid var(--color-link);
  cursor: pointer;
  transition: all 0.2s ease;
  display: inline-block;
}

.button-secondary:hover {
  background-color: var(--color-link);
  color: white;
}
```

### Footer

```css
.footer {
  background-color: var(--color-bg-secondary);
  padding: var(--space-section-sm) 0;
  color: var(--color-text-secondary);
}

.footer-section {
  margin-bottom: var(--space-8);
}

.footer-section-title {
  font-size: 12px;
  font-weight: var(--font-weight-semibold);
  color: var(--color-text-secondary);
  margin-bottom: var(--space-3);
  text-transform: none;
}

.footer-link {
  color: var(--color-text-secondary);
  font-size: 12px;
  text-decoration: none;
  display: block;
  margin-bottom: var(--space-2);
  transition: color 0.2s ease;
}

.footer-link:hover {
  color: var(--color-text-primary);
}

.footer-legal {
  font-size: 12px;
  color: var(--color-text-tertiary);
  line-height: 1.5;
  margin-top: var(--space-8);
  padding-top: var(--space-6);
  border-top: 1px solid var(--color-border-subtle);
}
```

---

## ✨ Animações e Transições

### Duração e Easing

```css
/* Durações */
--duration-instant: 100ms;
--duration-fast: 200ms;
--duration-normal: 300ms;
--duration-slow: 500ms;

/* Easing functions */
--ease-in: cubic-bezier(0.4, 0, 1, 1);
--ease-out: cubic-bezier(0, 0, 0.2, 1);
--ease-in-out: cubic-bezier(0.4, 0, 0.2, 1);
--ease-apple: cubic-bezier(0.28, 0.11, 0.32, 1);
```

### Transições Comuns

```css
/* Hover suave */
.smooth-hover {
  transition: all var(--duration-normal) var(--ease-apple);
}

/* Fade in */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.fade-in {
  animation: fadeIn var(--duration-slow) var(--ease-out);
}

/* Scale hover */
.scale-hover {
  transition: transform var(--duration-normal) var(--ease-apple);
}

.scale-hover:hover {
  transform: scale(1.05);
}
```

### Scroll Animations

```css
/* Parallax suave */
.parallax {
  transform: translateY(calc(var(--scroll) * 0.5px));
  will-change: transform;
}

/* Reveal on scroll */
.reveal {
  opacity: 0;
  transform: translateY(40px);
  transition: opacity var(--duration-slow) var(--ease-out),
              transform var(--duration-slow) var(--ease-out);
}

.reveal.active {
  opacity: 1;
  transform: translateY(0);
}
```

---

## 🎨 Iconografia

### Sistema de Ícones

```css
/* Tamanhos de ícones */
--icon-xs: 16px;
--icon-sm: 20px;
--icon-md: 24px;
--icon-lg: 32px;
--icon-xl: 48px;

/* Estilo de ícone */
.icon {
  display: inline-block;
  width: var(--icon-md);
  height: var(--icon-md);
  stroke-width: 1.5px;
  stroke: currentColor;
  fill: none;
}

/* Ícone com link */
.icon-link {
  color: var(--color-link);
  transition: color var(--duration-fast) var(--ease-out);
}

.icon-link:hover {
  color: var(--color-link-hover);
}
```

### Diretrizes de Uso

- **Estilo**: SF Symbols ou ícones lineares minimalistas
- **Peso**: 1.5px de stroke para consistência
- **Cor**: Herdar cor do texto ou usar cor de link
- **Alinhamento**: Centralizado vertical e horizontalmente
- **Espaçamento**: 8px de margem ao redor

---

## 🖼️ Imagens e Mídia

### Proporções de Imagem

```css
/* Aspect ratios comuns */
--aspect-square: 1 / 1;
--aspect-portrait: 3 / 4;
--aspect-landscape: 4 / 3;
--aspect-widescreen: 16 / 9;
--aspect-ultrawide: 21 / 9;
--aspect-hero: 2 / 1;

/* Container com aspect ratio */
.media-container {
  position: relative;
  width: 100%;
  aspect-ratio: var(--aspect-widescreen);
  overflow: hidden;
  border-radius: 18px;
}

.media-container img,
.media-container video {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
}
```

### Otimização de Imagem

```html
<!-- Responsive images com srcset -->
<img
  src="product-medium.jpg"
  srcset="
    product-small.jpg 640w,
    product-medium.jpg 1024w,
    product-large.jpg 1920w
  "
  sizes="
    (max-width: 640px) 100vw,
    (max-width: 1024px) 80vw,
    1200px
  "
  alt="MacBook Neo"
  loading="lazy"
>
```

### Vídeo Hero

```css
.video-hero {
  position: relative;
  width: 100%;
  height: 100vh;
  overflow: hidden;
}

.video-hero video {
  position: absolute;
  top: 50%;
  left: 50%;
  min-width: 100%;
  min-height: 100%;
  transform: translate(-50%, -50%);
  object-fit: cover;
}

.video-hero-overlay {
  position: relative;
  z-index: 1;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  height: 100%;
  text-align: center;
  color: white;
}
```

---

## ♿ Acessibilidade

### Contraste de Cores

```css
/* Garantir contraste mínimo WCAG AA (4.5:1 para texto normal) */

/* ✅ Bom contraste */
.text-primary-on-light {
  color: #1D1D1F; /* Contraste 16:1 com branco */
  background-color: #FFFFFF;
}

.text-light-on-dark {
  color: #F5F5F7; /* Contraste 15:1 com preto */
  background-color: #000000;
}

/* ⚠️ Usar apenas para texto grande (18px+) */
.text-secondary-on-light {
  color: #6E6E73; /* Contraste 4.6:1 */
  background-color: #FFFFFF;
}
```

### Foco e Navegação por Teclado

```css
/* Indicador de foco visível */
:focus-visible {
  outline: 2px solid var(--color-link);
  outline-offset: 2px;
  border-radius: 4px;
}

/* Remover outline padrão mas manter para teclado */
:focus {
  outline: none;
}

/* Skip to content link */
.skip-to-content {
  position: absolute;
  top: -100px;
  left: 0;
  background: var(--color-link);
  color: white;
  padding: var(--space-2) var(--space-4);
  text-decoration: none;
  z-index: 9999;
}

.skip-to-content:focus {
  top: 0;
}
```

### ARIA e Semântica

```html
<!-- Navigation com ARIA -->
<nav aria-label="Navegação principal">
  <ul role="list">
    <li><a href="/">Início</a></li>
    <li><a href="/mac">Mac</a></li>
    <li><a href="/ipad">iPad</a></li>
  </ul>
</nav>

<!-- Botão com estado -->
<button
  aria-label="Adicionar ao carrinho"
  aria-pressed="false"
>
  Comprar
</button>

<!-- Imagem decorativa -->
<img src="decoration.svg" alt="" aria-hidden="true">

<!-- Imagem informativa -->
<img src="product.jpg" alt="MacBook Neo visto de frente com tela exibindo cores vibrantes">
```

### Motion Preferences

```css
/* Respeitar preferência de movimento reduzido */
@media (prefers-reduced-motion: reduce) {
  *,
  *::before,
  *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
    scroll-behavior: auto !important;
  }
}
```

---

## 📱 Responsividade

### Mobile First

```css
/* Base: Mobile (0-639px) */
.hero-title {
  font-size: 40px;
  line-height: 1.1;
}

/* Tablet (640px+) */
@media (min-width: 640px) {
  .hero-title {
    font-size: 56px;
    line-height: 1.07;
  }
}

/* Desktop (1024px+) */
@media (min-width: 1024px) {
  .hero-title {
    font-size: 80px;
    line-height: 1.05;
  }
}

/* Desktop XL (1280px+) */
@media (min-width: 1280px) {
  .hero-title {
    font-size: 96px;
  }
}
```

### Touch Targets

```css
/* Tamanho mínimo de 44x44px para elementos tocáveis */
.touch-target {
  min-width: 44px;
  min-height: 44px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}
```

---

## 🎯 Utilitários CSS

### Texto

```css
/* Alinhamento */
.text-left { text-align: left; }
.text-center { text-align: center; }
.text-right { text-align: right; }

/* Peso */
.font-light { font-weight: var(--font-weight-light); }
.font-regular { font-weight: var(--font-weight-regular); }
.font-medium { font-weight: var(--font-weight-medium); }
.font-semibold { font-weight: var(--font-weight-semibold); }
.font-bold { font-weight: var(--font-weight-bold); }

/* Truncate */
.truncate {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* Line clamp */
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
```

### Layout

```css
/* Display */
.block { display: block; }
.inline-block { display: inline-block; }
.flex { display: flex; }
.inline-flex { display: inline-flex; }
.grid { display: grid; }
.hidden { display: none; }

/* Flexbox */
.flex-row { flex-direction: row; }
.flex-col { flex-direction: column; }
.items-center { align-items: center; }
.items-start { align-items: flex-start; }
.items-end { align-items: flex-end; }
.justify-center { justify-content: center; }
.justify-between { justify-content: space-between; }
.justify-around { justify-content: space-around; }

/* Gaps */
.gap-2 { gap: var(--space-2); }
.gap-4 { gap: var(--space-4); }
.gap-6 { gap: var(--space-6); }
.gap-8 { gap: var(--space-8); }
```

### Espaçamento

```css
/* Margin */
.m-0 { margin: 0; }
.m-auto { margin: auto; }
.mt-4 { margin-top: var(--space-4); }
.mb-4 { margin-bottom: var(--space-4); }
.ml-4 { margin-left: var(--space-4); }
.mr-4 { margin-right: var(--space-4); }
.mx-4 { margin-left: var(--space-4); margin-right: var(--space-4); }
.my-4 { margin-top: var(--space-4); margin-bottom: var(--space-4); }

/* Padding */
.p-0 { padding: 0; }
.pt-4 { padding-top: var(--space-4); }
.pb-4 { padding-bottom: var(--space-4); }
.pl-4 { padding-left: var(--space-4); }
.pr-4 { padding-right: var(--space-4); }
.px-4 { padding-left: var(--space-4); padding-right: var(--space-4); }
.py-4 { padding-top: var(--space-4); padding-bottom: var(--space-4); }
```

---

## 📦 Implementação

### Arquivo CSS Completo

```css
/* design-system.css */

:root {
  /* Cores */
  --color-bg-primary: #FFFFFF;
  --color-bg-secondary: #F5F5F7;
  --color-bg-dark: #000000;
  --color-text-primary: #1D1D1F;
  --color-text-secondary: #6E6E73;
  --color-text-tertiary: #86868B;
  --color-link: #0071E3;
  --color-border: #D2D2D7;
  
  /* Tipografia */
  --font-family-display: "SF Pro Display", -apple-system, BlinkMacSystemFont, sans-serif;
  --font-family-text: "SF Pro Text", -apple-system, BlinkMacSystemFont, sans-serif;
  
  /* Espaçamento */
  --space-1: 4px;
  --space-2: 8px;
  --space-3: 12px;
  --space-4: 16px;
  --space-6: 24px;
  --space-8: 32px;
  
  /* Animações */
  --duration-fast: 200ms;
  --duration-normal: 300ms;
  --ease-apple: cubic-bezier(0.28, 0.11, 0.32, 1);
}

/* Reset básico */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: var(--font-family-text);
  color: var(--color-text-primary);
  background-color: var(--color-bg-primary);
  line-height: 1.47059;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

/* Utilitários importantes */
.container {
  width: 100%;
  max-width: 1024px;
  margin: 0 auto;
  padding: 0 var(--space-4);
}

@media (min-width: 1024px) {
  .container {
    padding: 0 var(--space-8);
  }
}
```

---

## 🚀 Exemplos de Uso

### Página de Produto

```html
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MacBook Neo - Apple (Brasil)</title>
  <link rel="stylesheet" href="design-system.css">
</head>
<body>
  <!-- Hero Section -->
  <section class="hero">
    <div class="container">
      <h1 class="hero-title">MacBook Neo</h1>
      <p class="hero-subtitle">Um Mac incrível. Uma escolha inteligente.</p>
      <div class="hero-actions">
        <a href="#" class="link-primary">Saiba mais</a>
        <a href="#" class="link-secondary">Ver preços</a>
      </div>
    </div>
  </section>

  <!-- Product Grid -->
  <section class="py-section">
    <div class="container">
      <div class="product-grid">
        <div class="product-card">
          <div class="product-card-media">
            <img src="macbook-pro.jpg" alt="MacBook Pro">
          </div>
          <div class="product-card-content">
            <h3 class="product-card-title">MacBook Pro</h3>
            <p class="product-card-description">Agora com M5, M5 Pro e M5 Max.</p>
            <div class="product-card-actions">
              <a href="#" class="link-primary">Saiba mais</a>
              <a href="#" class="link-secondary">Comprar</a>
            </div>
          </div>
        </div>
        <!-- Mais cards... -->
      </div>
    </div>
  </section>
</body>
</html>
```

---

## 📚 Recursos Adicionais

### Fontes

- **SF Pro Display**: Para títulos e headings grandes
- **SF Pro Text**: Para corpo de texto e UI
- Download: [Apple Developer Fonts](https://developer.apple.com/fonts/)

### Ferramentas

- **Figma**: Para design e prototipagem
- **Sketch**: Alternativa para macOS
- **ColorSlurp**: Para capturar cores exatas
- **xScope**: Para medir espaçamentos e dimensões

### Diretrizes Oficiais

- [Apple Human Interface Guidelines](https://developer.apple.com/design/)
- [Apple Design Resources](https://developer.apple.com/design/resources/)
- [SF Symbols](https://developer.apple.com/sf-symbols/)

---

## ✅ Checklist de Implementação

### Design
- [ ] Cores do brand implementadas
- [ ] Tipografia SF Pro configurada
- [ ] Sistema de espaçamento aplicado
- [ ] Grid responsivo funcionando
- [ ] Componentes principais criados

### Acessibilidade
- [ ] Contraste de cores validado (WCAG AA)
- [ ] Navegação por teclado funcionando
- [ ] ARIA labels implementados
- [ ] Foco visível em todos elementos interativos
- [ ] Preferência de movimento reduzido respeitada

### Performance
- [ ] Imagens otimizadas e responsivas
- [ ] CSS minificado
- [ ] Lazy loading implementado
- [ ] Fontes carregadas de forma eficiente
- [ ] Animações com will-change quando necessário

### Responsividade
- [ ] Mobile first approach
- [ ] Breakpoints consistentes
- [ ] Touch targets adequados (44x44px mínimo)
- [ ] Testes em diferentes dispositivos

---

## 📝 Notas de Versão

### v1.0.0 (2026-04-17)
- ✨ Design system inicial baseado em apple.com/br
- 🎨 Sistema completo de cores e tipografia
- 📐 Grid e layout responsivo
- 🧩 Componentes principais
- ♿ Diretrizes de acessibilidade
- 📱 Mobile first approach

---

**Última atualização**: 17 de abril de 2026  
**Mantido por**: Equipe de Design  
**Versão**: 1.0.0
