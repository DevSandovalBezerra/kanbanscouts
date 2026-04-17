# Plano de Modernização de Interface do Sistema

Este documento descreve o plano arquitetural e as diretrizes de UX/UI para a modernização do sistema, buscando uma interface pautada na **fluidez, modernidade, elegância e design enxuto**, utilizando como base os princípios estruturados no `system.design.md` atual e as avaliações das diretrizes de nível de produção (UI-UX Pro Max, Vercel Web Design Guidelines e Frontend Design Avançado).

---

## 1. Direção Estética e Mandato de Design

Seguindo as premissas do framework de **Frontend Design**, evitamos layouts "genéricos" focando em um design intencional e expressivo:

- **Elegância Minimalista**: O visual será extremamente refinado, usando do espaço negativo (espaços em branco) como elemento ativo de design.
- **Aesthetic Anchor (Diferenciação)**: Tipografia clara e contrastante que fala mais alto que qualquer elemento gráfico desnecessário (Baseado na adoção correta do SF Pro Display e SF Pro Text).
- **Redução Cognitiva (Design Enxuto)**: Menos elementos competindo pela atenção. Todo detalhe visual deve ter um propósito (remover bordas supérfluas, fundos conflitantes).

---

## 2. Padrões de UX e UI Pro Max (Interação e Experiência)

Integramos o checklist *UI-UX Pro Max* para assegurar qualidade Enterprise, aplicando a padronização baseada no nosso projeto:

* **Minimalismo Orientado a Dados**: Aplicação do padrão de "Data-Dense Dashboard" sem agredir visualmente. Gráficos zoomáveis e destaque na passagem de linha (hover) sem poluição visual.
* **Micro-interações (Fluidez)**: 
  * Todas as interações em botões e links usarão transições suaves (`transition-colors duration-200 ease-out`).
  * Efeitos como *Tooltips* rápidos ao hover e zoom sutil em imagens (`hover:scale-105`) com animação apenas na opacidade e no transform.
* **Componentes Profissionais**:
  * Substituição imediata de "Emojis" em UI por pacotes de SVG vetoriais (ex: Lucide ou Heroicons).
  * Todos os elementos clicáveis de destaque precisam de `cursor-pointer` com feedback imediato de foco e interação.

---

## 3. Diretrizes de Qualidade e Performance (Vercel Web Design Guidelines)

Com base nos padrões de alta performance na engenharia de frontend da Vercel, devemos auditar e reconstruir elementos seguindo regras estritas:

### A. Acessibilidade (Crucial para um sistema moderno)
- Elementos sem texto (como botões apenas com ícone) devem possuir propriedades obrigatórias: `aria-label`.
- Navegação de formulários sem tropeços: Uso severo de `label` vinculada ao campo, ou `aria-label`.
- Estados de foco controlados: Trocar o comportamentos genéricos para `:focus-visible:ring-2` (eliminando contornos azuis nativos acidentais em cliques manuais, usando apenas na navegação por teclado).
- Processos baseados em estado de espera (Toasts e Atualizações assíncronas) devem utilizar propriedades nativas como `aria-live="polite"`.

### B. Formulários Otimizados
- Botões de Submit (`Enviar`) devem ser desativados e apresentar um ícone "spinner" de modo imediato ao clique.
- Erros apresentem contextualização visual com foco retornado no input do primeiro erro encontrado.
- Remover o atributo indiscriminado `transition: all`. Refatorar para transicionar apenas propriedades de compositor (ex: `transform` ou `opacity`). 

---

## 4. Evolução do CSS e Cores (`system.design.md`)

Manteremos a hierarquia já firmada da identidade inspirada na clareza do formato *"Apple-like"*:
- As transições para o Tema Escuro (Dark mode) não usaram valores extremistas, usando `#000000` balanceado para fundo primário e variantes transparentes como `#1D1D1F` em *surfaces*/cards para profundidade.
- A Tipografia terá balanço (`text-wrap: balance` e `text-pretty`) para os Headings.
- Contrastes devem sempre atestar `WCAG AA` como especificado no check UI/UX Pro Max.

---

## 5. Roteiro (Roadmap) de Ação no Código

**Fase 1: Ajuste de Fundações (CSS e Globais)**
- Atualizar a base de variáveis CSS assegurando todos os tokens de Design (Espaços `space-X`, Cores `color-text-*`, Animações `duration-*`).
- Consolidar as fontes globais aplicando pesos estruturais otimizados.

**Fase 2: Remoção de "Technical Debt" (Limpeza Visual)**
- Auditar TODO o sistema e trocar glifos textuais (emojis espalhados, etc.) pelos ícones oficiais SVG (24x24px, traço 1.5px).
- Remover transições "artificiais" pesadas (`transition: all`), substituindo por animações otimizadas para GPU.

**Fase 3: Atualizações Core e Interatividade (Vercel Standard)**
- Revisar `Forms` em todo o sistema. Adicionar placeholders concisos (terminados em "…").
- Refatorar cartões em grids, otimizando espaçamento negativo para parecerem respiráveis ao redimensionar a tela.
- Inserir tratativa de foco visível em todos os menus/links.

**Fase 4: Acessibilidade e Fechamento**
- Verificações de cor WCAG final.
- Garantir comportamento amigável em preferência `prefers-reduced-motion` no sistema do usuário final.
- Otimizar imagens com carregamento preemptivo em componentes de mídia acima da dobra da página e `loading="lazy"` para os demais.

---

*Baseado nas competências: `frontend-design`, `ui-ux-pro-max`, `vercel/web-design-guidelines`.*
