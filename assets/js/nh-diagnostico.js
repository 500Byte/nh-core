const CFG = window.nhDiagnostico || {};
const WEBHOOK_URL = CFG.webhookUrl || '';
const QUIZ_URL = CFG.quizUrl || 'https://www.normahana.com/diagnostico-estilo/';
const IMG = CFG.img || {};

const ACTS = [
  { roman:'Acto I', name:'El color que te habita', img:IMG.act1, start:0, end:4,
    sub:'Tu piel, tus ojos y tu cabello guardan una paleta que espera por ti. Cuatro momentos para revelarla.' },
  { roman:'Acto II', name:'La forma que te viste', img:IMG.act2, start:4, end:6,
    sub:'Tus elecciones de color revelan mucho de ti. Ahora conoceremos la proporción que armoniza contigo y cómo resaltarla con cada corte.' },
  { roman:'Acto III', name:'Tu manera de vestir', img:IMG.act3, start:6, end:12,
    sub:'Tu silueta se ha definido. Ahora, seis instantes para encontrar el arquetipo puro que habla por ti. Sin respuestas incorrectas.' }
];

const PROGRESS_MSGS = {
  0:'Definiendo tu color...',
  1:'Cada tono importa...',
  2:'Tu paleta se va revelando...',
  3:'Vas conociendo tu luz...',
  4:'Estructurando tu silueta...',
  5:'Tus formas se definen...',
  6:'Descubriendo tu arquetipo...',
  7:'Tejiendo tu esencia...',
  8:'Construyendo tu perfil...',
  9:'Tus decisiones hablan...',
  10:'Casi tienes tu carta...',
  11:'Última señal...'
};

const questions = [
  { id:'ojos', dim:'color', axis:'subtono', weight:1, img:IMG.q1, title:'Tus ojos son…', options:[
    { label:'Café, miel o verde oliva', value:'calida' },
    { label:'Azules, grises o verde claro', value:'fria' }
  ]},
  { id:'cabello', dim:'color', axis:'subtono', weight:2, img:IMG.q2, title:'El color natural de tu cabello tiene reflejos…', options:[
    { label:'Dorados, cobrizos o rojizos', value:'calida' },
    { label:'Cenizos o grisáceos (sin tonos cálidos)', value:'fria' }
  ]},
  { id:'piel', dim:'color', axis:'subtono', weight:3, img:IMG.q3, title:'Mira las venas de tu muñeca con luz natural. ¿De qué color se ven?', options:[
    { label:'Verdosas', value:'calida' },
    { label:'Azules o moradas', value:'fria' }
  ]},
  { id:'claridad', dim:'color', axis:'claridad', img:IMG.q4, title:'Tu cabello natural (sin tinte) es más bien…', options:[
    { label:'Claro — rubio, castaño claro o pelirrojo claro', value:'claro' },
    { label:'Oscuro — castaño oscuro, negro o muy oscuro', value:'oscuro' }
  ]},
  { id:'hombros_caderas', dim:'body', img:IMG.q5, title:'¿Cómo se comparan tus hombros con tus caderas?', options:[
    { label:'Son igual de anchos', value:'igual' },
    { label:'Mis caderas son más anchas', value:'caderas_mayor' },
    { label:'Mis hombros son más anchos', value:'hombros_mayor' }
  ]},
  { id:'cintura', dim:'body', img:IMG.q6, title:'¿Cómo describirías tu cintura?', options:[
    { label:'Muy marcada y definida', value:'marcada' },
    { label:'Poco marcada, casi recta', value:'recta' },
    { label:'Mi zona media es la parte más voluminosa', value:'volumen' }
  ]},
  { id:'dia_a_dia', dim:'style', img:IMG.q7, title:'Tu día a día es principalmente…', options:[
    { label:'Profesional y estructurado', value:'Clásico' },
    { label:'Sensible y detallista', value:'Romántico' },
    { label:'Cambiante, nunca igual dos días', value:'Creativo' },
    { label:'Cómodo y funcional', value:'Natural' },
    { label:'Sereno y refinado', value:'Elegante' }
  ]},
  { id:'prenda', dim:'style', img:IMG.q8, title:'La prenda que más te representa es…', options:[
    { label:'Un blazer bien cortado', value:'Clásico' },
    { label:'Un vestido con detalles delicados', value:'Romántico' },
    { label:'Una pieza con estampado o mezcla inesperada', value:'Creativo' },
    { label:'Un jean y una camiseta básica', value:'Natural' },
    { label:'Una pieza minimalista de líneas limpias', value:'Elegante' }
  ]},
  { id:'colores', dim:'style', img:IMG.q9, title:'Si solo pudieras vestir con 3 colores el resto del año…', options:[
    { label:'Neutros clásicos: negro, blanco, gris', value:'Clásico' },
    { label:'Pasteles y tonos suaves', value:'Romántico' },
    { label:'Colores vibrantes y mezclas atrevidas', value:'Creativo' },
    { label:'Tonos tierra y naturales', value:'Natural' },
    { label:'Blanco, negro y un solo color de acento', value:'Elegante' }
  ]},
  { id:'palabra', dim:'style', img:IMG.q10, title:'¿Con qué palabra te identificas más?', options:[
    { label:'Fuerza', value:'Clásico' },
    { label:'Dulzura', value:'Romántico' },
    { label:'Libertad', value:'Natural' },
    { label:'Elegancia', value:'Elegante' }
  ]},
  { id:'evento', dim:'style', img:IMG.q11, title:'Para un evento especial prefieres…', options:[
    { label:'Un look impecable y coordinado', value:'Clásico' },
    { label:'Algo romántico, con encaje o volantes', value:'Romántico' },
    { label:'Una pieza statement que nadie más tendría', value:'Creativo' },
    { label:'Algo cómodo pero cuidado', value:'Natural' },
    { label:'Un vestido elegante de corte simple', value:'Elegante' }
  ]},
  { id:'emocion', dim:'style', img:IMG.q12, title:'¿Qué quieres proyectar al vestir?', options:[
    { label:'Seguridad y autoridad', value:'Clásico' },
    { label:'Ternura y cercanía', value:'Romántico' },
    { label:'Inspiración y originalidad', value:'Creativo' },
    { label:'Paz y autenticidad', value:'Natural' },
    { label:'Sofisticación y calma', value:'Elegante' }
  ]}
];

const ARCHETYPES = {
  'Clásico':   { name:'La Primera de la Ciudad', tag:'Orden, autoridad y estructura', img:IMG.arch_clasico, desc:'Transmites firmeza, profesionalismo y prudencia. Las líneas limpias y los cortes bien definidos son tu lenguaje natural.' },
  'Romántico': { name:'La Perla de la Bahía', tag:'Dulzura, delicadeza y afectividad', img:IMG.arch_romantico, desc:'Tu naturaleza amorosa y empática se refleja en texturas suaves, detalles delicados y siluetas femeninas.' },
  'Creativo':  { name:'La Musa del Puerto', tag:'Visión, inspiración y originalidad', img:IMG.arch_creativo, desc:'Mezclas texturas, colores y formas con libertad. Tu estilo habla de una mujer con sensibilidad artística.' },
  'Natural':   { name:'La Costeña Libre', tag:'Paz, sencillez y autenticidad', img:IMG.arch_natural, desc:'Valoras lo cómodo y funcional. Tu estilo es práctico, relajado y genuino — con el mar como espejo.' },
  'Elegante':  { name:'La Dama del Malecón', tag:'Refinamiento, madurez y señorío', img:IMG.arch_elegante, desc:'Sobria y sofisticada, transmites excelencia sin esfuerzo. Menos es más.' }
};

const SHAPE_INFO = {
  'Reloj de arena':     'Marca tu cintura con cinturones o cortes princesa. Evita prendas muy sueltas que escondan tu proporción natural.',
  'Triángulo o Pera':   'Da protagonismo a tu parte superior con escotes, estampados u hombreras suaves.',
  'Triángulo invertido':'Equilibra con faldas de volumen y colores claros abajo; evita hombreras marcadas.',
  'Rectángulo':         'Crea curvas visuales con peplum, faldas acampanadas o un cinturón marcado.',
  'Óvalo o Manzana':    'Alarga tu figura con líneas verticales, cuellos en V y tejidos fluidos.'
};

const SEASON_INFO = {
  'Primavera': { colors:['#F2A65A','#F2C14E','#4FB0A5','#E8DCC4'], label:'Coral, salmón, turquesa y dorado' },
  'Verano':    { colors:['#D8A7B1','#B9A6C9','#A9C5DE','#C9C4B8'], label:'Rosado, lila, azul cielo y gris suave' },
  'Otoño':     { colors:['#B8632E','#C99A3A','#6E7B4F','#8A5A3B'], label:'Terracota, mostaza y verde oliva' },
  'Invierno':  { colors:['#1A1A1A','#8C1F2B','#FFFFFF','#1F3A5F'], label:'Negro, rojo rubí, blanco puro y azul marino' }
};

let screen = 'cover';
let qIndex = 0;
let actIndex = 0;
let answers = {};
let leadName = '';
let leadEmail = '';
let leadPhone = '';
let result = null;

function getColorSeason(subtono, claridad){
  if(subtono==='fria' && claridad==='claro') return 'Verano';
  if(subtono==='fria' && claridad==='oscuro') return 'Invierno';
  if(subtono==='calida' && claridad==='claro') return 'Primavera';
  return 'Otoño';
}
function getBodyShape(hc, cintura){
  if(hc==='hombros_mayor') return 'Triángulo invertido';
  if(hc==='caderas_mayor') return 'Triángulo o Pera';
  if(cintura==='marcada') return 'Reloj de arena';
  if(cintura==='recta') return 'Rectángulo';
  return 'Óvalo o Manzana';
}
function computeResult(){
  let calida=0, fria=0;
  questions.filter(q=>q.axis==='subtono').forEach(q=>{
    const v = answers[q.id];
    if(v==='calida') calida += q.weight; else fria += q.weight;
  });
  const subtono = calida >= fria ? 'calida' : 'fria';
  const season = getColorSeason(subtono, answers['claridad']);
  const shape = getBodyShape(answers['hombros_caderas'], answers['cintura']);
  const counts = {};
  questions.filter(q=>q.dim==='style').forEach(q=>{
    const v = answers[q.id];
    counts[v] = (counts[v]||0) + 1;
  });
  const style = Object.entries(counts).sort((a,b)=>b[1]-a[1])[0][0];
  const colorTotal = questions.filter(q=>q.axis==='subtono').reduce((s,q)=>s+q.weight, 0);
  const styleTotal = questions.filter(q=>q.dim==='style').length;
  return { season, shape, style, seasonVotes:{calida, fria, total:colorTotal}, styleVotes:counts, styleTotal };
}
function showToast(msg){
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.classList.add('show');
  clearTimeout(t._timer);
  t._timer = setTimeout(()=>t.classList.remove('show'), 3200);
}
function esc(s){
  return (s||'').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}
function track(eventName, payload){
  if(window.dataLayer) window.dataLayer.push(Object.assign({event:eventName}, payload));
}

const REDUCED = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
let navDir = 'forward';
let busy = false;
let activeScreen = null;

function getContent(){
  if(screen === 'cover') return renderCover();
  if(screen === 'chapter') return renderChapter();
  if(screen === 'quiz') return renderQuiz();
  if(screen === 'gate') return renderGate();
  return renderCard();
}

function render(){
  const root = document.getElementById('screens');
  while(root.children.length > 1) root.firstElementChild.remove();
  const oldEl = root.firstElementChild;
  const content = getContent();
  busy = false;

  if(screen === 'quiz') preloadFlow(qIndex + 1);
  if(screen === 'gate') preloadArchetypes();

  if(!oldEl || !window.gsap){
    root.innerHTML = content;
    bindEvents();
    activeScreen = root.firstElementChild;
    animateIntro(root.firstElementChild);
    return;
  }

  const wrap = document.createElement('div');
  wrap.innerHTML = content;
  const entering = wrap.firstElementChild;
  root.appendChild(entering);
  bindEvents(entering);
  activeScreen = entering;
  runTransition(oldEl, entering, screen, navDir);
}

function animateIntro(el){
  if(!el || !window.gsap || REDUCED) return;
  gsap.fromTo(el, {autoAlpha:0, y:18}, {autoAlpha:1, y:0, duration:0.6, ease:'power3.out'});
}

function runTransition(oldEl, entering, type, dir){
  oldEl.style.pointerEvents = 'none';
  const tl = gsap.timeline({ onComplete: () => oldEl.remove() });
  const dirY = dir === 'back' ? -1 : 1;
  const quizSwap = oldEl.classList.contains('quiz') && entering.classList.contains('quiz');

  if(REDUCED){
    gsap.set(entering, {autoAlpha:0});
    tl.to(entering, {autoAlpha:1, duration:0.2, ease:'power1.out'}, 0)
      .to(oldEl, {autoAlpha:0, duration:0.2, ease:'power1.out'}, 0);
    return;
  }

  if(quizSwap){
    const oldMedia = oldEl.querySelector('.quiz-media');
    const newMedia = entering.querySelector('.quiz-media');
    if(oldMedia && newMedia){
      const dirX = dir === 'back' ? 1 : -1;
      gsap.set(newMedia, {x: (dirX * -100) + '%'});
      tl.to(oldMedia, {x: (dirX * 100) + '%', duration:0.45, ease:'power2.inOut'}, 0)
        .to(newMedia, {x:'0%', duration:0.45, ease:'power2.inOut'}, 0);
    } else {
      gsap.set(entering, {autoAlpha:0});
      tl.to(oldEl, {autoAlpha:0, duration:0.18, ease:'power1.out'}, 0.02)
        .to(entering, {autoAlpha:1, duration:0.32, ease:'power1.out'}, 0);
    }
  } else {
    tl.to(oldEl, {autoAlpha:0, y: dirY * -18, duration:0.2, ease:'power1.in'}, 0.02);
    gsap.set(entering, {autoAlpha:0, y: dirY * 26});
    tl.to(entering, {autoAlpha:1, y:0, duration:0.42, ease:'power3.out'}, 0);
  }

  polishScreen(tl, entering, type, dirY, oldEl, quizSwap);
}

const STAGGER_SEL = '.cover-brand, .cover h1, .cover-sub, .cover-pillars, #start-btn, .chapter-roman, .chapter h2, .chapter-sub, #act-start-btn, .seal-wrap, .gate h2, .gate-lede, .form-field, .form-checkbox, #reveal-btn, #gate-back, .card-hero-content, .result-tag, .match-line, .card-desc, .info-cards, .share-row, .nh-btn, .restart-link';

function polishScreen(tl, entering, type, dirY, oldEl, quizSwap){
  const isQuiz = entering.classList.contains('quiz');
  const img = entering.querySelector('.quiz-media .bg') || entering.querySelector('.bg');
  if(img && !quizSwap){
    gsap.set(img, {scale:1.07, filter:'blur(8px)'});
    tl.to(img, {scale:1, filter:'blur(0px)', duration:0.55, ease:'power2.out'}, 0);
  }

  const opts = entering.querySelectorAll('.option');
  const fill = entering.querySelector('.q-progress-fill');
  const oldFill = oldEl.querySelector('.q-progress-fill');

  if(quizSwap){
    if(opts.length){
      gsap.set(opts, {autoAlpha:0, y: 10 * dirY});
      tl.to(opts, {autoAlpha:1, y:0, duration:0.24, ease:'power2.out', stagger:0.028}, 0.16);
    }
  } else if(isQuiz){
    if(opts.length){
      gsap.set(opts, {autoAlpha:0, y: 12 * dirY});
      tl.to(opts, {autoAlpha:1, y:0, duration:0.26, ease:'power2.out', stagger:0.035}, 0.18);
    }
  } else {
    const els = entering.querySelectorAll(STAGGER_SEL);
    if(els.length){
      gsap.set(els, {autoAlpha:0, y: 10 * dirY});
      tl.to(els, {autoAlpha:1, y:0, duration:0.3, ease:'power2.out', stagger:0.035}, 0.12);
    }
  }

  if(fill && oldFill){
    const oldW = parseInt(oldFill.style.width, 10) || 0;
    const newW = parseInt(fill.style.width, 10) || 0;
    gsap.set(fill, {width: oldW + '%'});
    tl.to(fill, {width: newW + '%', duration:0.5, ease:'power3.out'}, 0);
  }
}

function renderCover(){
  return `
    <div class="screen cover">
      <div class="bg" style="background-image:url('${IMG.cover}')"></div>
      <div class="scrim"></div>
      <div class="cover-content">
        <div class="cover-brand">Norma Hana</div>
        <div class="cover-mid">
          <h1>¿Qué prenda de Norma Hana eres?</h1>
          <p class="cover-sub">Un ritual de 12 momentos para descubrir la paleta que te habita, la silueta que te viste y el arquetipo de estilo que vive en ti.</p>
          <div class="cover-pillars">
            <span class="cover-pillar">Color</span>
            <span class="cover-pillar">Silueta</span>
            <span class="cover-pillar">Estilo</span>
          </div>
        </div>
        <button class="nh-btn nh-btn--primary" id="start-btn">Comenzar el juego <span class="btn-arrow">→</span></button>
        <p class="meta-note" style="color:rgba(255,255,255,0.7);">12 momentos · tu carta al final · menos de 3 minutos</p>
      </div>
    </div>`;
}

function renderChapter(){
  const act = ACTS[actIndex];
  return `
    <div class="screen chapter">
      <div class="bg" style="background-image:url('${act.img}')"></div>
      <div class="scrim scrim--deep"></div>
      <div class="chapter-content">
        <div class="chapter-roman">${act.roman}</div>
        <h2>${act.name}</h2>
        <p class="chapter-sub">${act.sub}</p>
        <button class="nh-btn nh-btn--primary" id="act-start-btn">Entrar al acto <span class="btn-arrow">→</span></button>
      </div>
    </div>`;
}

function renderQuiz(){
  const q = questions[qIndex];
  const pct = Math.round((qIndex / questions.length) * 100);
  const dimLabel = q.dim === 'color' ? 'Color' : q.dim === 'body' ? 'Silueta' : 'Estilo';
  return `
    <div class="screen quiz">
      <div class="quiz-media">
        <div class="bg" style="background-image:url('${q.img}')"></div>
        <div class="scrim"></div>
        <div class="quiz-top">
          <button class="back-btn" id="back-btn" aria-label="Atrás">←</button>
          <div class="q-progress"><div class="q-progress-fill" style="width:${pct}%"></div></div>
          <span class="q-count">${qIndex+1} / ${questions.length}</span>
        </div>
        <div class="quiz-title-wrap">
          <div class="q-eyebrow">${dimLabel}</div>
          <h2 class="quiz-title">${esc(q.title)}</h2>
        </div>
      </div>
      <div class="quiz-sheet">
        <div class="q-msg">${PROGRESS_MSGS[qIndex] || ''}</div>
        <div class="options">
          ${q.options.map((o,i) => `<button class="option" style="--i:${i}" data-value="${esc(o.value)}" data-qid="${q.id}">${esc(o.label)}</button>`).join('')}
        </div>
      </div>
    </div>`;
}

function renderGate(){
  if (!result) result = computeResult();
  const ar = ARCHETYPES[result.style];
  return `
    <div class="screen gate">
      <div class="gate-content">
        <div class="seal-wrap">
          <div class="seal"><div class="seal-text">Tu carta<br>te espera</div></div>
        </div>
        <h2>Tu carta está lista</h2>
        <div class="ornament" aria-hidden="true"><span class="ornament-line"></span><span class="ornament-diamond">✦</span><span class="ornament-line"></span></div>
        <div class="archetype-name">${esc(ar.name)}</div>
        <p class="archetype-desc">${esc(ar.desc)}</p>
        <p class="gate-lede">Tu patrón está tejido. Para llevarlo contigo, dinos a dónde enviar tu dossier.</p>
        <form id="lead-form" novalidate>
          <div class="form-field">
            <label class="form-label" for="lead-name">Nombre</label>
            <input class="form-input" type="text" id="lead-name" autocomplete="name" placeholder="Tu nombre" value="${esc(leadName)}">
          </div>
          <div class="form-field">
            <label class="form-label" for="lead-email">Correo electrónico</label>
            <input class="form-input" type="email" id="lead-email" autocomplete="email" placeholder="tucorreo@ejemplo.com" value="${esc(leadEmail)}">
            <div class="form-error" id="email-error">Ingresa un correo válido para ver tu carta.</div>
          </div>
          <div class="form-field">
            <label class="form-label" for="lead-phone">Teléfono</label>
            <input class="form-input" type="tel" id="lead-phone" autocomplete="tel" inputmode="tel" placeholder="300 123 4567" value="${esc(leadPhone)}">
            <div class="form-error" id="phone-error">Ingresa un celular válido (10 dígitos) para ver tu carta.</div>
          </div>
          <label class="form-checkbox">
            <input type="checkbox" id="lead-consent">
            <span>Acepto la <a href="https://www.normahana.com/politica-de-privacidad/" target="_blank" rel="noopener">Política de Privacidad</a> y el tratamiento de mis datos para recibir mi carta y contenido de la marca.</span>
          </label>
          <div class="form-error" id="consent-error">Debes aceptar la Política de Privacidad para ver tu carta.</div>
          <button class="nh-btn nh-btn--primary" id="reveal-btn" type="submit">Sellar mi carta</button>
        </form>
        <button class="gate-back" id="gate-back">← Volver al final del ritual</button>
        <p class="meta-note">No compartimos tus datos. Puedes darte de baja cuando quieras.</p>
      </div>
    </div>`;
}

function renderCard(){
  const r = result;
  const ar = ARCHETYPES[r.style];
  const season = SEASON_INFO[r.season];
  const sv = r.seasonVotes;
  const winner = sv.calida >= sv.fria ? 'calida' : 'fria';
  const votes = winner === 'calida' ? sv.calida : sv.fria;
  return `
    <div class="screen card-screen">
      <div class="card-hero">
        <div class="bg" style="background-image:url('${ar.img}')"></div>
        <div class="scrim"></div>
        <div class="card-hero-content">
          <div class="card-hero-label">La carta de estilo de</div>
          <div class="card-hero-name">${esc(leadName || 'ti')}</div>
          <div class="card-archetype">${esc(ar.name)}</div>
        </div>
      </div>
      <div class="card-body">
        <div class="result-tag">${esc(ar.tag)}</div>
        <p class="match-line">Tus señales: <b>${votes} de ${sv.total}</b> en la gama ${winner === 'calida' ? 'cálida' : 'fría'} · <b>${esc(ar.name)}</b> lideró <b>${r.styleVotes[r.style]}</b> de <b>${r.styleTotal}</b> momentos</p>
        <p class="card-desc">${esc(ar.desc)}</p>
        <div class="info-cards">
          <div class="info-card">
            <h3>Tu paleta · ${esc(r.season)}</h3>
            <div class="swatches">
              ${season.colors.map(c => `<span class="swatch" style="background:${c}"></span>`).join('')}
            </div>
            <p>${esc(season.label)}. Estos son los tonos que más armonizan con tu piel, ojos y cabello.</p>
          </div>
          <div class="info-card">
            <h3>Tu silueta · ${esc(r.shape)}</h3>
            <p>${esc(SHAPE_INFO[r.shape])}</p>
          </div>
        </div>
        <div class="share-row">
          <button class="share-btn" id="share-wa" aria-label="Compartir en WhatsApp">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.47 14.38c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.17-.17.2-.35.22-.64.07-.3-.15-1.26-.46-2.4-1.48-.88-.79-1.48-1.76-1.65-2.06-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.08-.15-.67-1.62-.92-2.22-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.8.37-.27.3-1.04 1.02-1.04 2.5 0 1.47 1.07 2.9 1.22 3.1.15.2 2.1 3.2 5.1 4.49.71.3 1.27.49 1.7.63.72.23 1.37.2 1.88.12.58-.09 1.76-.72 2-1.42.25-.7.25-1.3.18-1.42-.07-.13-.27-.2-.57-.35zM12.05 21.79h-.01a9.8 9.8 0 0 1-5-1.37l-.36-.21-3.7.97.99-3.61-.24-.37a9.77 9.77 0 1 1 8.32 4.6zm0-18.61a7.98 7.98 0 0 0-6.91 11.97l.47.78-.58 2.13 2.19-.57.75.45a7.98 7.98 0 1 0 4.08-14.76z"/></svg>
            WhatsApp
          </button>
          <button class="share-btn" id="share-ig" aria-label="Compartir en historia de Instagram">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 8.7a3.3 3.3 0 1 0 0 6.6 3.3 3.3 0 0 0 0-6.6z"/><rect x="3.5" y="3.5" width="17" height="17" rx="5"/><circle cx="17.3" cy="6.7" r="1.1" fill="currentColor" stroke="none"/></svg>
            Historia
          </button>
          <button class="share-btn" id="share-cp" aria-label="Copiar resultado">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="9" y="9" width="11" height="11" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
            Copiar
          </button>
        </div>
        <a class="nh-btn nh-btn--primary" href="https://wa.me/573043510019" target="_blank" rel="noopener">Agenda tu asesoría 1:1</a>
        <a class="nh-btn nh-btn--secondary" style="margin-top:10px;" href="https://www.normahana.com/tienda/">Descubre prendas para tu perfil</a>
        <button class="restart-link" id="restart-btn">↻ Repetir el juego</button>
        <p class="meta-note">Este resultado es una guía orientativa y puede tener margen de error — nada reemplaza el ojo experto de una asesoría personalizada.</p>
        <p class="meta-note">Tu carta también llegó a ${esc(leadEmail)}.</p>
      </div>
    </div>`;
}

function phoneOk(v){
  const digits = v.replace(/[^0-9]/g,'');
  if(digits.length === 10) return /^3\d{9}$/.test(digits);
  if(digits.length === 12) return /^57/.test(digits) && /^3\d{9}$/.test(digits.slice(2));
  return false;
}

function buildPayload(){
  return {
    name: leadName,
    email: leadEmail,
    phone: leadPhone,
    consent: true,
    season: result.season,
    shape: result.shape,
    style: result.style,
    answers: answers
  };
}

async function sendLead(){
  const payload = buildPayload();
  console.log('[nh-diagnostico] payload', JSON.stringify(payload, null, 2));
  if(!WEBHOOK_URL){
    showToast('Prototipo — lead listo para enviar (mira la consola).');
    return;
  }
  try{
    const res = await fetch(WEBHOOK_URL, {
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body:JSON.stringify(payload)
    });
    if(res.ok) showToast('¡Tu carta quedó sellada! Te escribiremos pronto.');
    else showToast('Guardamos tu carta. Puedes verla aquí.');
  }catch(e){
    showToast('Guardamos tu carta. Puedes verla aquí.');
  }
}

function confetti(colors){
  if(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  const stage = document.querySelector('.stage');
  const frag = document.createDocumentFragment();
  for(let i=0;i<40;i++){
    const c = document.createElement('span');
    c.className = 'confetti';
    c.style.background = colors[i % colors.length];
    c.style.left = (Math.random()*100)+'%';
    c.style.setProperty('--tx', (Math.random()*180-90)+'px');
    c.style.setProperty('--r', (Math.random()*540-270)+'deg');
    c.style.setProperty('--d', (Math.random()*700+500)+'ms');
    c.style.setProperty('--delay', (Math.random()*450)+'ms');
    c.style.width = (Math.random()*5+4)+'px';
    c.style.height = (Math.random()*9+6)+'px';
    frag.appendChild(c);
  }
  stage.appendChild(frag);
  setTimeout(()=>{ stage.querySelectorAll('.confetti').forEach(x=>x.remove()); }, 2600);
}

function revealCard(){
  if (!result) result = computeResult();
  track('nh_diagnostico_submit', { nh_name: leadName, nh_email: leadEmail, nh_result: result.style, nh_consent: true });
  const overlay = document.getElementById('stamp-overlay');
  const seal = document.getElementById('stamp-seal');
  overlay.classList.add('show');
  requestAnimationFrame(() => {
    requestAnimationFrame(() => seal.classList.add('stamping'));
  });
  setTimeout(() => {
    overlay.classList.remove('show');
    seal.classList.remove('stamping');
    navDir = 'forward';
    screen = 'card';
    render();
    confetti([...SEASON_INFO[result.season].colors, '#C2B280', '#4A5D4E']);
    sendLead();
  }, 850);
}

function shareText(){
  const ar = ARCHETYPES[result.style];
  return `Mi carta de estilo: ${ar.name} ✦ Paleta ${result.season} ✦ Silueta ${result.shape}. ¿Cuál es la tuya? Descúbrela en Norma Hana → ${QUIZ_URL}`;
}

function shareWhatsApp(){
  window.open('https://wa.me/?text=' + encodeURIComponent(shareText()), '_blank');
  track('nh_diagnostico_share', { method: 'whatsapp' });
}

function copyResult(){
  navigator.clipboard.writeText(shareText()).then(() => {
    showToast('Resultado copiado ✓ Pégalo donde quieras.');
  }).catch(() => showToast('No se pudo copiar. Inténtalo de nuevo.'));
  track('nh_diagnostico_share', { method: 'copy' });
}

async function drawStoryCard(){
  const ar = ARCHETYPES[result.style];
  const season = SEASON_INFO[result.season];
  const canvas = document.createElement('canvas');
  canvas.width = 1080;
  canvas.height = 1920;
  const ctx = canvas.getContext('2d');
  const img = new Image();
  img.crossOrigin = 'anonymous';
  await new Promise((res, rej) => { img.onload = res; img.onerror = rej; img.src = ar.img.replace('w=900', 'w=1080'); });
  ctx.drawImage(img, 0, 0, 1080, 1920);
  const g = ctx.createLinearGradient(0, 300, 0, 1920);
  g.addColorStop(0, 'rgba(15,15,15,0.10)');
  g.addColorStop(0.55, 'rgba(15,15,15,0.35)');
  g.addColorStop(1, 'rgba(15,15,15,0.90)');
  ctx.fillStyle = g;
  ctx.fillRect(0, 0, 1080, 1920);
  try{
    await document.fonts.load('600 72px "Playfair Display"');
    await document.fonts.load('400 64px "Playfair Display"');
    await document.fonts.load('700 44px "DM Sans"');
  }catch(e){}
  ctx.textAlign = 'center';
  ctx.fillStyle = '#C2B280';
  ctx.font = '700 44px "DM Sans"';
  if('letterSpacing' in ctx) ctx.letterSpacing = '16px';
  ctx.fillText('NORMA HANA', 540, 190);
  if('letterSpacing' in ctx) ctx.letterSpacing = '0px';
  ctx.fillStyle = '#F5F0D8';
  ctx.font = 'italic 400 58px "Playfair Display"';
  ctx.fillText('La carta de estilo de', 540, 780);
  ctx.font = '600 112px "Playfair Display"';
  ctx.fillText((leadName || 'ti').slice(0, 16), 540, 920);
  ctx.fillStyle = '#C2B280';
  ctx.font = '700 70px "Playfair Display"';
  ctx.fillText(ar.name.slice(0, 22), 540, 1070);
  ctx.fillStyle = '#FFFFFF';
  ctx.font = '500 36px "DM Sans"';
  ctx.fillText(ar.tag, 540, 1160);
  const cols = season.colors;
  const sw = 72, gap = 36;
  const total = cols.length * sw + (cols.length - 1) * gap;
  let x = 540 - total / 2 + sw / 2;
  ctx.save();
  ctx.shadowColor = 'rgba(0,0,0,0.4)';
  ctx.shadowBlur = 18;
  cols.forEach(c => {
    ctx.fillStyle = c;
    ctx.beginPath();
    ctx.arc(x, 1320, sw / 2, 0, Math.PI * 2);
    ctx.fill();
    x += sw + gap;
  });
  ctx.restore();
  ctx.fillStyle = '#F5F0D8';
  ctx.font = '500 38px "DM Sans"';
  ctx.fillText('Paleta ' + result.season + ' · Silueta ' + result.shape, 540, 1470);
  ctx.fillStyle = '#D4C99A';
  ctx.font = '700 44px "DM Sans"';
  ctx.fillText('NORMANAHA.COM', 540, 1720);
  ctx.fillStyle = 'rgba(255,255,255,0.9)';
  ctx.font = '500 32px "DM Sans"';
  ctx.fillText('Descubre la tuya →', 540, 1790);
  return new Promise(res => canvas.toBlob(res, 'image/png'));
}

async function shareStory(){
  try{
    const blob = await drawStoryCard();
    const file = new File([blob], 'carta-estilo.png', { type: 'image/png' });
    if(navigator.canShare && navigator.canShare({ files: [file] })){
      await navigator.share({ files: [file], title: 'Mi carta de estilo', text: shareText() });
    } else {
      const a = document.createElement('a');
      a.href = URL.createObjectURL(blob);
      a.download = 'carta-estilo.png';
      a.click();
      URL.revokeObjectURL(a.href);
      showToast('Carta descargada ✨ Súbela a tu historia.');
    }
    track('nh_diagnostico_share', { method: 'instagram' });
  }catch(e){}
}

const preloaded = new Set();
function preloadImage(u){
  if(!u || preloaded.has(u)) return;
  preloaded.add(u);
  const i = new Image();
  i.src = u;
}
function preloadFlow(idx){
  [IMG.cover, IMG.act1, IMG.act2, IMG.act3].forEach(preloadImage);
  for(let k = Math.max(0, idx); k <= Math.min(questions.length - 1, idx + 2); k++){
    if(questions[k] && questions[k].img) preloadImage(questions[k].img);
  }
}
function preloadArchetypes(){
  Object.keys(IMG).filter(k => k.indexOf('arch_') === 0).forEach(k => preloadImage(IMG[k]));
}

function bindEvents(scope = document){
  const q = sel => scope.querySelector(sel);

  const startBtn = q('#start-btn');
  if(startBtn) startBtn.onclick = () => { navDir='forward'; screen='chapter'; actIndex=0; render(); };

  const actStart = q('#act-start-btn');
  if(actStart) actStart.onclick = () => { navDir='forward'; screen='quiz'; qIndex=ACTS[actIndex].start; render(); };

  const backBtn = q('#back-btn');
  if(backBtn) backBtn.onclick = () => {
    if(busy || backBtn.closest('.screen') !== activeScreen) return;
    busy = true;
    navDir='back';
    if(qIndex > 0){ qIndex -= 1; render(); }
    else { screen='cover'; render(); }
  };

  const gateBack = q('#gate-back');
  if(gateBack) gateBack.onclick = () => {
    if(busy || gateBack.closest('.screen') !== activeScreen) return;
    busy = true;
    navDir='back';
    qIndex = questions.length - 1;
    screen='quiz';
    render();
  };

  scope.querySelectorAll('.option').forEach(btn => {
    btn.onclick = () => {
      if(busy || btn.closest('.screen') !== activeScreen) return;
      busy = true;
      navDir='forward';
      const qid = btn.getAttribute('data-qid');
      const val = btn.getAttribute('data-value');
      answers[qid] = val;
      btn.classList.add('selected');
      setTimeout(() => {
        const act = ACTS.find(a => qIndex >= a.start && qIndex < a.end);
        if(qIndex < act.end - 1){
          qIndex += 1;
          render();
        } else if(actIndex < ACTS.length - 1){
          actIndex += 1;
          screen = 'chapter';
          render();
        } else {
          screen = 'gate';
          render();
        }
      }, 200);
    };
  });

  const leadForm = q('#lead-form');
  if(leadForm) leadForm.onsubmit = (e) => {
    e.preventDefault();
    const nameInput = q('#lead-name');
    const emailInput = q('#lead-email');
    const phoneInput = q('#lead-phone');
    const consentInput = q('#lead-consent');
    const emailVal = emailInput.value.trim();
    const phoneVal = phoneInput.value.trim();
    const emailOk = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailVal);
    const phoneOkVal = phoneOk(phoneVal);

    q('#email-error').style.display = emailOk ? 'none' : 'block';
    emailInput.classList.toggle('is-error', !emailOk);
    q('#phone-error').style.display = phoneOkVal ? 'none' : 'block';
    phoneInput.classList.toggle('is-error', !phoneOkVal);
    q('#consent-error').style.display = consentInput.checked ? 'none' : 'block';
    consentInput.classList.toggle('is-error', !consentInput.checked);

    if(!emailOk || !phoneOkVal || !consentInput.checked) return;

    leadName = nameInput.value.trim();
    leadEmail = emailVal;
    leadPhone = phoneVal;
    const btn = q('#reveal-btn');
    btn.disabled = true;
    btn.classList.add('is-loading');
    btn.innerHTML = '<span class="spin"></span>Sellando tu carta…';
    setTimeout(revealCard, 600);
  };

  const shareWa = q('#share-wa');
  if(shareWa) shareWa.onclick = shareWhatsApp;
  const shareIg = q('#share-ig');
  if(shareIg) shareIg.onclick = shareStory;
  const shareCp = q('#share-cp');
  if(shareCp) shareCp.onclick = copyResult;

  const restartBtn = q('#restart-btn');
  if(restartBtn) restartBtn.onclick = () => {
    navDir='back';
    answers = {};
    leadName = '';
    leadEmail = '';
    leadPhone = '';
    result = null;
    qIndex = 0;
    actIndex = 0;
    screen = 'cover';
    render();
  };
}

preloadFlow(0);
render();
