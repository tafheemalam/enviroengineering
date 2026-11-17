import { Link } from 'react-router-dom';
import { ArrowRight, Calendar, Quote, Star } from 'lucide-react';
import { serviceHref, formatDate } from '../utils/format';
import { getIcon } from '../utils/icons';
import { Reveal } from './ui';

export function ServiceCard({ service, delay = 0, headingLevel = 'h3' }) {
  const H = headingLevel;
  const Icon = getIcon(service.icon);
  const isDetail = service.page_type === 'detail';
  const href = serviceHref(service);
  return (
    <Reveal as="article" className="service-card" delay={delay}>
      <Link to={href} className="service-card-media" tabIndex={-1} aria-hidden="true">
        {service.image ? (
          <img src={service.image} alt={service.image_alt} loading="lazy" width="800" height="520" />
        ) : (
          <span className="media-fallback" />
        )}
      </Link>
      <div className="service-card-body">
        <span className="service-card-icon" aria-hidden="true"><Icon size={24} /></span>
        <H className="service-card-title">
          <Link to={href}>{service.title}</Link>
        </H>
        <p>{service.short_description}</p>
        <Link to={href} className="link-arrow" aria-label={`${isDetail ? 'Read more about' : 'Enquire about'} ${service.title}`}>
          {isDetail ? 'Read More' : 'Contact Us'} <ArrowRight size={16} aria-hidden="true" />
        </Link>
      </div>
    </Reveal>
  );
}

export function PostCard({ post, delay = 0 }) {
  return (
    <Reveal as="article" className="post-card" delay={delay}>
      <Link to={`/insights/${post.slug}`} className="post-card-media" tabIndex={-1} aria-hidden="true">
        {post.image ? <img src={post.image} alt={post.image_alt} loading="lazy" width="800" height="520" /> : <span className="media-fallback" />}
      </Link>
      <div className="post-card-body">
        {post.published_at && (
          <span className="post-meta"><Calendar size={14} aria-hidden="true" /> <time dateTime={post.published_at}>{formatDate(post.published_at)}</time></span>
        )}
        <h3><Link to={`/insights/${post.slug}`}>{post.title}</Link></h3>
        <p>{post.excerpt}</p>
        <Link to={`/insights/${post.slug}`} className="link-arrow" aria-label={`Read article: ${post.title}`}>
          Read Article <ArrowRight size={16} aria-hidden="true" />
        </Link>
      </div>
    </Reveal>
  );
}

export function TestimonialCard({ t, delay = 0 }) {
  return (
    <Reveal as="figure" className="testimonial-card" delay={delay}>
      <Quote size={32} className="quote-icon" aria-hidden="true" />
      <div className="stars" aria-label={`Rated ${t.rating} out of 5`}>
        {Array.from({ length: 5 }).map((_, i) => (
          <Star key={i} size={16} aria-hidden="true" fill={i < t.rating ? 'currentColor' : 'none'} />
        ))}
      </div>
      <blockquote>{t.content}</blockquote>
      <figcaption>
        <strong>{t.client_name}</strong>
        <span>{[t.client_role, t.company].filter(Boolean).join(', ')}</span>
      </figcaption>
    </Reveal>
  );
}
