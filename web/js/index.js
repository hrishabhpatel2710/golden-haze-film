/* --- Custom Cursor --- */
const dot  = document.getElementById('cursor-dot');
const ring = document.getElementById('cursor-ring');
let mx = 0, my = 0, rx = 0, ry = 0;
document.addEventListener('mousemove', e => { mx = e.clientX; my = e.clientY; });
function animCursor() {
  dot.style.left  = mx + 'px'; dot.style.top = my + 'px';
  rx += (mx - rx) * 0.12; ry += (my - ry) * 0.12;
  ring.style.left = rx + 'px'; ring.style.top = ry + 'px';
  requestAnimationFrame(animCursor);
}
animCursor();
document.querySelectorAll('a, button, .service-card, .project-card, .pkg-card').forEach(el => {
  el.addEventListener('mouseenter', () => {
    dot.style.width = '14px'; dot.style.height = '14px';
    ring.style.width = '52px'; ring.style.height = '52px';
    ring.style.borderColor = 'rgba(200,164,106,0.8)';
  });
  el.addEventListener('mouseleave', () => {
    dot.style.width = '8px'; dot.style.height = '8px';
    ring.style.width = '36px'; ring.style.height = '36px';
    ring.style.borderColor = 'rgba(200,164,106,0.5)';
  });
});

/* --- Nav scroll --- */
const nav = document.getElementById('nav');
window.addEventListener('scroll', () => {
  nav.classList.toggle('scrolled', window.scrollY > 60);
  const prog = (window.scrollY / (document.body.scrollHeight - window.innerHeight)) * 100;
  document.getElementById('progress-bar').style.width = prog + '%';
});

/* --- Scroll reveal --- */
const reveals = document.querySelectorAll('.reveal');
const revealObs = new IntersectionObserver((entries) => {
  entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('visible'); } });
}, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
reveals.forEach(el => revealObs.observe(el));

/* --- Gold Particles Canvas --- */
const canvas = document.getElementById('particles');
const ctx = canvas.getContext('2d');
let W, H, particles = [];

function resize() {
  W = canvas.width  = canvas.offsetWidth;
  H = canvas.height = canvas.offsetHeight;
}
resize();
window.addEventListener('resize', () => { resize(); initParticles(); });

class Particle {
  constructor() { this.reset(); }
  reset() {
    this.x  = Math.random() * W;
    this.y  = Math.random() * H;
    this.r  = Math.random() * 1.5 + 0.3;
    this.vx = (Math.random() - 0.5) * 0.3;
    this.vy = (Math.random() - 0.5) * 0.25 - 0.12;
    this.a  = Math.random() * 0.4 + 0.1;
    this.life = 1; this.decay = Math.random() * 0.003 + 0.001;
  }
  draw() {
    ctx.beginPath(); ctx.arc(this.x, this.y, this.r, 0, Math.PI*2);
    ctx.fillStyle = `rgba(200,164,106,${this.a * this.life})`;
    ctx.fill();
    this.x += this.vx; this.y += this.vy; this.life -= this.decay;
    if (this.life <= 0 || this.y < -10) this.reset();
  }
}

function initParticles() {
  particles = Array.from({ length: 80 }, () => new Particle());
  particles.forEach(p => p.y = Math.random() * H); // spread initially
}
initParticles();

function animParticles() {
  ctx.clearRect(0, 0, W, H);
  particles.forEach(p => p.draw());
  requestAnimationFrame(animParticles);
}
animParticles();

/* --- Testimonials Slider --- */
const track = document.getElementById('tTrack');
const dots  = document.querySelectorAll('.t-dot');
let current = 0;
const total = dots.length;

function goTo(n) {
  current = (n + total) % total;
  track.style.transform = `translateX(-${current * 100}%)`;
  dots.forEach((d, i) => d.classList.toggle('active', i === current));
}
document.getElementById('tNext').addEventListener('click', () => goTo(current + 1));
document.getElementById('tPrev').addEventListener('click', () => goTo(current - 1));
dots.forEach((d, i) => d.addEventListener('click', () => goTo(i)));
setInterval(() => goTo(current + 1), 5500);

/* --- Parallax hero on mouse move --- */
const heroContent = document.querySelector('.hero-content');
document.getElementById('hero').addEventListener('mousemove', e => {
  const cx = (e.clientX / window.innerWidth  - 0.5) * 16;
  const cy = (e.clientY / window.innerHeight - 0.5) * 10;
  heroContent.style.transform = `translate(${cx * 0.4}px, ${cy * 0.3}px)`;
});

/* --- Magnetic service cards --- */
document.querySelectorAll('.service-card').forEach(card => {
  card.addEventListener('mousemove', e => {
    const r = card.getBoundingClientRect();
    const x = (e.clientX - r.left - r.width  / 2) * 0.05;
    const y = (e.clientY - r.top  - r.height / 2) * 0.05;
    card.style.transform = `translate(${x}px, ${y}px)`;
  });
  card.addEventListener('mouseleave', () => { card.style.transform = ''; });
});

/* --- Project card parallax --- */
document.querySelectorAll('.project-card').forEach(card => {
  card.addEventListener('mousemove', e => {
    const r = card.getBoundingClientRect();
    const x = (e.clientX - r.left - r.width / 2)  / r.width  * 15;
    const y = (e.clientY - r.top  - r.height / 2) / r.height * 10;
    card.querySelector('.project-bg').style.transform = `scale(1.06) translate(${x * 0.5}px, ${y * 0.5}px)`;
  });
  card.addEventListener('mouseleave', () => {
    card.querySelector('.project-bg').style.transform = '';
  });
});

/* --- Smooth scroll for anchor links --- */
document.querySelectorAll('a[href^="#"]').forEach(a => {
  a.addEventListener('click', e => {
    const target = document.querySelector(a.getAttribute('href'));
    if (target) {
      e.preventDefault();
      target.scrollIntoView({ behavior: 'smooth' });
    }
  });
});

/* --- Form submit feedback --- */
const contactForm = document.getElementById('contact-form');
if (contactForm) {
  contactForm.addEventListener('submit', function() {
    const btn = contactForm.querySelector('.form-submit');
    if (btn) {
      btn.disabled = true;
      btn.textContent = 'Sending…';
    }
  });

  if (contactForm.querySelector('.form-notice--success')) {
    const btn = contactForm.querySelector('.form-submit');
    if (btn) {
      btn.textContent = '✦ Message Sent — We\'ll Be in Touch';
      btn.style.background = 'rgba(200,164,106,0.2)';
      btn.style.color = 'var(--gold)';
      btn.style.border = '1px solid var(--gold)';
    }
  }
}
