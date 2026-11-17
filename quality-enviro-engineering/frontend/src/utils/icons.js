// Explicit icon map keeps the bundle small (no "import *" of all icons).
// Keep in sync with the icon list in backend/admin/includes/icons.php.
import {
  Leaf, Activity, ShieldCheck, ClipboardCheck, HardHat, Wrench, Recycle, Droplets, Wind,
  Target, Eye, Award, BadgeCheck, ThumbsUp, Smile, Cog, MessagesSquare, Search, ClipboardList,
  Scale, Users, Lightbulb, FileText, Clock, Factory, Gauge, Building2, Globe, Sprout,
  FlaskConical, Zap, Settings, Handshake, TreePine, Waves, Hammer, Ruler, HeartHandshake,
  Microscope, Truck, CheckCircle2, Star, Flame, ShieldAlert, Thermometer,
} from 'lucide-react';

export const ICONS = {
  Leaf, Activity, ShieldCheck, ClipboardCheck, HardHat, Wrench, Recycle, Droplets, Wind,
  Target, Eye, Award, BadgeCheck, ThumbsUp, Smile, Cog, MessagesSquare, Search, ClipboardList,
  Scale, Users, Lightbulb, FileText, Clock, Factory, Gauge, Building2, Globe, Sprout,
  FlaskConical, Zap, Settings, Handshake, TreePine, Waves, Hammer, Ruler, HeartHandshake,
  Microscope, Truck, CheckCircle2, Star, Flame, ShieldAlert, Thermometer,
};

export function getIcon(name, fallback = CheckCircle2) {
  return ICONS[name] || fallback;
}
