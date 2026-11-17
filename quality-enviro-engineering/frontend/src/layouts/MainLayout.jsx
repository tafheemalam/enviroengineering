import { Outlet } from 'react-router-dom';
import Header from '../components/Header';
import Footer from '../components/Footer';
import { WhatsAppButton, BackToTop, RouteEffects } from '../components/Floating';

export default function MainLayout() {
  return (
    <>
      <a href="#main" className="skip-link">Skip to main content</a>
      <RouteEffects />
      <Header />
      <main id="main" tabIndex={-1}>
        <Outlet />
      </main>
      <Footer />
      <WhatsAppButton />
      <BackToTop />
    </>
  );
}
