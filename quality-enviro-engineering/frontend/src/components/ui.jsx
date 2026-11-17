import { useEffect, useRef, useState } from 'react';
import { Link } from 'react-router-dom';
import { ArrowRight, AlertTriangle, ChevronRight, Home } from 'lucide-react';
import { getIcon } from '../utils/icons';
import { isInternal } from '../utils/format';

/** Fade/slide content in when it scrolls into view. */
export function Reveal({ as: Tag = 'div', delay = 0, className = '', children, ...rest }) {
  const ref = useRef(null);
  const [visible, setVisible] = useState(false);
  useEffect(() => {
    const el = ref.current;
    if (!el || !('IntersectionObserver' in window)) {
      setVisible(true);
      return;
    }
    const io = new IntersectionObserver(
      ([entry]) => {
        if (entry.isIntersecting) {
          setVisible(true);
          io.disconnect();
        }
      },
      { rootMargin: '0px 0px -60px 0px', threshold: 0.05 }
    );
    io.observe(el);
    return () => io.disconnect();
  }, []);
  return (
    <Tag ref={ref} className={`reveal ${visible ? 'is-visible' : ''} ${className}`} style={{ transitionDelay: `${delay}ms` }} {...rest}>
      {children}
    </Tag>
  );
}

export function SectionHeading({ eyebrow, title, subtitle, center = true, as: H = 'h2', light = false }) {
  if (!title && !eyebrow) return null;
  return (
    <Reveal className={`section-heading ${center ? 'text-center mx-auto' : ''} ${light ? 'light' : ''}`}>
      {eyebrow && <span className="eyebrow">{eyebrow}</span>}
      {title && <H>{title}</H>}
      {subtitle && <p className="lead-sub">{subtitle}</p>}
    </Reveal>
  );
}

/** Button that renders <Link> for internal paths and <a> for external URLs. */
export function SmartLink({ href, className = 'btn-brand', children, arrow = false }) {
  if (!href) return null;
  const inner = (
    <>
      {children}
      {arrow && <ArrowRight size={18} aria-hidden="true" />}
    </>
  );
  return isInternal(href) ? (
    <Link to={href} className={className}>{inner}</Link>
  ) : (
    <a href={href} className={className} target="_blank" rel="noopener noreferrer">{inner}</a>
  );
}

export function IconBadge({ name, size = 26, className = '' }) {
  const Icon = getIcon(name);
  return (
    <span className={`icon-badge ${className}`} aria-hidden="true">
      <Icon size={size} />
    </span>
  );
}

export function Loader({ label = 'Loading…', full = false }) {
  return (
    <div className={`loader-wrap ${full ? 'full' : ''}`} role="status" aria-live="polite">
      <span className="spinner" aria-hidden="true" />
      <span className="visually-hidden">{label}</span>
    </div>
  );
}

export function ErrorState({ error, onRetry }) {
  return (
    <div className="error-state container" role="alert">
      <AlertTriangle size={40} aria-hidden="true" />
      <h2>Something went wrong</h2>
      <p>{error?.message || 'We could not load this content.'}</p>
      {onRetry && (
        <button type="button" className="btn-brand" onClick={onRetry}>Try again</button>
      )}
    </div>
  );
}

/** Inner-page banner with H1 and breadcrumbs. */
export function PageHeader({ eyebrow, title, subtitle, crumbs = [] }) {
  return (
    <section className="page-header">
      <div className="container">
        {crumbs.length > 0 && (
          <nav aria-label="Breadcrumb">
            <ol className="breadcrumbs">
              <li><Link to="/"><Home size={14} aria-hidden="true" /> <span>Home</span></Link></li>
              {crumbs.map((c, i) => (
                <li key={c.to}>
                  <ChevronRight size={14} aria-hidden="true" />
                  {i === crumbs.length - 1 ? <span aria-current="page">{c.label}</span> : <Link to={c.to}>{c.label}</Link>}
                </li>
              ))}
            </ol>
          </nav>
        )}
        {eyebrow && <span className="eyebrow light">{eyebrow}</span>}
        <h1>{title}</h1>
        {subtitle && <p className="lead-sub">{subtitle}</p>}
      </div>
    </section>
  );
}

export function Paragraphs({ items = [], className = '' }) {
  return items.map((p, i) => (
    <p key={i} className={className}>{p}</p>
  ));
}
