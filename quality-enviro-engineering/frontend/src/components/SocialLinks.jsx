import { Facebook, Linkedin, Instagram, Youtube, Twitter, MessageCircle, Globe } from 'lucide-react';
import { useSite } from '../hooks/useSite';

const MAP = {
  facebook: Facebook,
  linkedin: Linkedin,
  instagram: Instagram,
  youtube: Youtube,
  x: Twitter,
  twitter: Twitter,
  whatsapp: MessageCircle,
};

export default function SocialLinks({ size = 18, className = '' }) {
  const { social } = useSite();
  if (!social?.length) return null;
  return (
    <ul className={`social-links ${className}`}>
      {social.map((s) => {
        const Icon = MAP[s.platform] || Globe;
        return (
          <li key={s.platform}>
            <a href={s.url} target="_blank" rel="noopener noreferrer" aria-label={`${s.label} (opens in a new tab)`} title={s.label}>
              <Icon size={size} aria-hidden="true" />
            </a>
          </li>
        );
      })}
    </ul>
  );
}
