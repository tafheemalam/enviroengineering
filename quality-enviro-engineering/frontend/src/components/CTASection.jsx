import { Reveal, SmartLink } from './ui';

const DEFAULT = {
  title: 'Looking for Reliable Environmental & Engineering Solutions?',
  subtitle: 'Tell us about your project and our team will get back to you with the right approach.',
  button_text: 'Request a Quote',
  button_link: '/request-a-quote',
  button2_text: 'Contact Us',
  button2_link: '/contact',
};

export default function CTASection({ section }) {
  const s = { ...DEFAULT, ...(section || {}) };
  return (
    <section className="cta-section" aria-labelledby="cta-title">
      <div className="container">
        <Reveal className="cta-inner">
          <div>
            <h2 id="cta-title">{s.title}</h2>
            {s.subtitle && <p>{s.subtitle}</p>}
          </div>
          <div className="cta-actions">
            {s.button_text && <SmartLink href={s.button_link} className="btn-accent" arrow>{s.button_text}</SmartLink>}
            {s.button2_text && <SmartLink href={s.button2_link} className="btn-outline-light-brand">{s.button2_text}</SmartLink>}
          </div>
        </Reveal>
      </div>
    </section>
  );
}
