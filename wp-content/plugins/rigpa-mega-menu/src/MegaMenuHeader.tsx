import { useCallback, useEffect, useId, useRef, useState } from "react";
import type { MenuSection } from "./types";

interface MegaMenuHeaderProps {
  menus: MenuSection[];
  lang: "english" | "german";
  transparent?: boolean;
}

function useMediaQuery(query: string): boolean {
  const [matches, setMatches] = useState(() => {
    if (typeof window === "undefined") {
      return false;
    }
    return window.matchMedia(query).matches;
  });

  useEffect(() => {
    const mql = window.matchMedia(query);
    const handler = (event: MediaQueryListEvent) => setMatches(event.matches);
    setMatches(mql.matches);
    mql.addEventListener("change", handler);
    return () => mql.removeEventListener("change", handler);
  }, [query]);

  return matches;
}

function MenuPanel({
  menu,
  lang,
  panelId,
}: {
  menu: MenuSection;
  lang: "english" | "german";
  panelId: string;
}) {
  const learnMore = lang === "german" ? "Mehr erfahren →" : "Learn more →";

  return (
    <div
      id={panelId}
      className={`rigpa-mega-menu-panel ${
        menu.featured ? "rigpa-mega-menu-panel--with-featured" : ""
      }`}
    >
      <div className="rigpa-mega-menu-panel-links">
        {menu.items.map((item, idx) => (
          <a key={idx} href={item.url} className="rigpa-mega-menu-panel-link">
            <span className="rigpa-mega-menu-panel-link-title">
              {item.title}
            </span>
            {item.description && (
              <span className="rigpa-mega-menu-panel-link-desc">
                {item.description}
              </span>
            )}
          </a>
        ))}
      </div>

      {menu.featured && (
        <div className="rigpa-mega-menu-panel-featured">
          <div className="rigpa-mega-menu-panel-featured-image-wrap">
            <img
              src={menu.featured.image}
              alt={menu.featured.title}
              className="rigpa-mega-menu-panel-featured-image"
            />
          </div>
          <div className="rigpa-mega-menu-panel-featured-title">
            {menu.featured.title}
          </div>
          <div className="rigpa-mega-menu-panel-featured-desc">
            {menu.featured.description}
          </div>
          <a
            href={menu.featured.url}
            className="rigpa-mega-menu-panel-featured-cta"
          >
            {learnMore}
          </a>
        </div>
      )}
    </div>
  );
}

function MobileSectionLinks({
  menu,
  id,
}: {
  menu: MenuSection;
  id: string;
}) {
  return (
    <ul id={id} className="rigpa-mega-menu-mobile-links">
      {menu.items.map((item, idx) => (
        <li key={idx} className="rigpa-mega-menu-mobile-link-item">
          <a href={item.url} className="rigpa-mega-menu-mobile-link">
            <span className="rigpa-mega-menu-mobile-link-title">
              {item.title}
            </span>
            {item.description && (
              <span className="rigpa-mega-menu-mobile-link-desc">
                {item.description}
              </span>
            )}
          </a>
        </li>
      ))}
    </ul>
  );
}

export default function MegaMenuHeader({
  menus,
  lang,
  transparent = true,
}: MegaMenuHeaderProps) {
  const baseId = useId().replace(/:/g, "");
  const rootRef = useRef<HTMLDivElement>(null);
  const hoverTimeoutRef = useRef<ReturnType<typeof setTimeout> | null>(null);
  const itemRefs = useRef<(HTMLDivElement | null)[]>([]);
  const isDesktop = useMediaQuery("(min-width: 768px)");

  const PANEL_WIDTH = 890;
  const PANEL_OFFSET_LEFT = -20; // nudge slightly left of the trigger button
  const SCREEN_MARGIN = 12;      // minimum gap from viewport edges

  const [activeIndex, setActiveIndex] = useState<number | null>(null);
  const [renderedIndex, setRenderedIndex] = useState<number | null>(null);
  const [panelVisible, setPanelVisible] = useState(false);
  const [panelLeft, setPanelLeft] = useState<number>(0);
  const [mobileOpen, setMobileOpen] = useState(false);
  const [mobileRendered, setMobileRendered] = useState(false);
  const [mobileVisible, setMobileVisible] = useState(false);
  const [mobileExpandedIndex, setMobileExpandedIndex] = useState<number | null>(null);

  const calculatePanelLeft = useCallback((index: number) => {
    const el = itemRefs.current[index];
    if (!el) return 0;

    const rect = el.getBoundingClientRect();
    // Ideal position: align left edge of panel slightly left of the trigger
    let left = PANEL_OFFSET_LEFT;

    const panelScreenLeft = rect.left + left;
    const panelScreenRight = panelScreenLeft + PANEL_WIDTH;

    // If panel would bleed off the right edge, pull it left
    if (panelScreenRight > window.innerWidth - SCREEN_MARGIN) {
      left -= panelScreenRight - (window.innerWidth - SCREEN_MARGIN);
    }

    // If panel would bleed off the left edge, push it right
    if (rect.left + left < SCREEN_MARGIN) {
      left = SCREEN_MARGIN - rect.left;
    }

    return left;
  }, []);

  const closeAll = useCallback(() => {
    setActiveIndex(null);
    setMobileOpen(false);
    setMobileExpandedIndex(null);
  }, []);

  const clearHoverTimeout = useCallback(() => {
    if (hoverTimeoutRef.current !== null) {
      clearTimeout(hoverTimeoutRef.current);
      hoverTimeoutRef.current = null;
    }
  }, []);

  const openDesktopPanel = useCallback(
    (index: number) => {
      if (!isDesktop) {
        return;
      }
      clearHoverTimeout();
      setPanelLeft(calculatePanelLeft(index));
      setActiveIndex(index);
    },
    [clearHoverTimeout, isDesktop, calculatePanelLeft]
  );

  const scheduleCloseDesktopPanel = useCallback(() => {
    if (!isDesktop) {
      return;
    }
    clearHoverTimeout();
    hoverTimeoutRef.current = setTimeout(() => {
      setActiveIndex(null);
    }, 150);
  }, [clearHoverTimeout, isDesktop]);

  useEffect(() => {
    if (activeIndex !== null) {
      setRenderedIndex(activeIndex);
      const frame = requestAnimationFrame(() => setPanelVisible(true));
      return () => cancelAnimationFrame(frame);
    }

    setPanelVisible(false);
    const timer = window.setTimeout(() => setRenderedIndex(null), 280);
    return () => window.clearTimeout(timer);
  }, [activeIndex]);

  useEffect(() => {
    if (mobileOpen) {
      setMobileRendered(true);
      const frame = requestAnimationFrame(() => setMobileVisible(true));
      return () => cancelAnimationFrame(frame);
    }

    setMobileVisible(false);
    const timer = window.setTimeout(() => setMobileRendered(false), 280);
    return () => window.clearTimeout(timer);
  }, [mobileOpen]);

  useEffect(() => {
    const handleKeyDown = (event: KeyboardEvent) => {
      if (event.key === "Escape") {
        closeAll();
      }
    };

    document.addEventListener("keydown", handleKeyDown);
    return () => document.removeEventListener("keydown", handleKeyDown);
  }, [closeAll]);

  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (!rootRef.current?.contains(event.target as Node)) {
        closeAll();
      }
    };

    if (activeIndex !== null || mobileOpen) {
      document.addEventListener("mousedown", handleClickOutside);
      return () => document.removeEventListener("mousedown", handleClickOutside);
    }
  }, [activeIndex, mobileOpen, closeAll]);

  useEffect(() => {
    if (!isDesktop) {
      setActiveIndex(null);
    } else {
      setMobileOpen(false);
      setMobileExpandedIndex(null);
    }
  }, [isDesktop]);

  useEffect(() => {
    return () => clearHoverTimeout();
  }, [clearHoverTimeout]);

  const menuLabel = lang === "german" ? "Menü" : "Menu";
  const openMenuLabel = lang === "german" ? "Menü öffnen" : "Open menu";
  const closeMenuLabel = lang === "german" ? "Menü schließen" : "Close menu";

  const headerClass = transparent
    ? "rigpa-mega-menu-header rigpa-mega-menu-header--transparent"
    : "rigpa-mega-menu-header rigpa-mega-menu-header--solid";

  return (
    <div ref={rootRef} className={headerClass}>
      <div className="rigpa-mega-menu-inner">
        <div className="rigpa-mega-menu-header-row">
          {isDesktop ? (
            <nav className="rigpa-mega-menu-desktop-nav" aria-label={menuLabel}>
              {menus.map((menu, index) => {
                const panelId = `${baseId}-panel-${index}`;
                const isOpen = activeIndex === index;
                const isRendered = renderedIndex === index;
                const hasItems = menu.items.length > 0;

                if (!hasItems) {
                  return (
                    <div
                      key={menu.label}
                      className="rigpa-mega-menu-nav-item"
                    >
                      <a
                        href={menu.url || "#"}
                        className="rigpa-mega-menu-nav-btn"
                      >
                        {menu.label}
                      </a>
                    </div>
                  );
                }

                return (
                  <div
                    key={menu.label}
                    ref={(el) => { itemRefs.current[index] = el; }}
                    className="rigpa-mega-menu-nav-item"
                    onMouseEnter={() => openDesktopPanel(index)}
                    onMouseLeave={scheduleCloseDesktopPanel}
                  >
                    <button
                      type="button"
                      className={`rigpa-mega-menu-nav-btn ${
                        isOpen ? "rigpa-mega-menu-nav-btn--active" : ""
                      }`}
                      aria-expanded={isOpen}
                      aria-controls={panelId}
                      aria-haspopup="true"
                      onFocus={() => openDesktopPanel(index)}
                      onClick={() =>
                        setActiveIndex(isOpen ? null : index)
                      }
                    >
                      {menu.label}
                    </button>

                    {isRendered && (
                      <div
                        className={`rigpa-mega-menu-dropdown ${
                          panelVisible ? "rigpa-mega-menu-dropdown--open" : ""
                        }`}
                        style={{ left: `${panelLeft}px` }}
                      >
                        <div className="rigpa-mega-menu-dropdown-inner">
                          <MenuPanel
                            menu={menu}
                            lang={lang}
                            panelId={panelId}
                          />
                        </div>
                      </div>
                    )}
                  </div>
                );
              })}
            </nav>
          ) : (
            <button
              type="button"
              className="rigpa-mega-menu-mobile-toggle"
              aria-expanded={mobileOpen}
              aria-controls={`${baseId}-mobile-menu`}
              onClick={() => {
                setMobileOpen((open) => !open);
                setMobileExpandedIndex(null);
              }}
            >
              <span className="rigpa-mega-menu-sr-only">
                {mobileOpen ? closeMenuLabel : openMenuLabel}
              </span>
              <svg
                className="rigpa-mega-menu-mobile-toggle-icon"
                width="18"
                height="18"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                strokeWidth="2"
                aria-hidden="true"
              >
                {mobileOpen ? (
                  <path strokeLinecap="round" d="M6 6l12 12M18 6L6 18" />
                ) : (
                  <>
                    <path strokeLinecap="round" d="M4 7h16" />
                    <path strokeLinecap="round" d="M4 12h16" />
                    <path strokeLinecap="round" d="M4 17h16" />
                  </>
                )}
              </svg>
              <span className="rigpa-mega-menu-mobile-toggle-label">{menuLabel}</span>
            </button>
          )}
        </div>
      </div>

      {!isDesktop && mobileRendered && (
        <div
          id={`${baseId}-mobile-menu`}
          className={`rigpa-mega-menu-mobile-panel ${
            mobileVisible ? "rigpa-mega-menu-mobile-panel--open" : ""
          }`}
        >
          <nav aria-label={menuLabel} className="rigpa-mega-menu-mobile-nav">
            {menus.map((menu, index) => {
              const bodyId = `${baseId}-mobile-body-${index}`;
              const isExpanded = mobileExpandedIndex === index;
              const hasItems = menu.items.length > 0;

              if (!hasItems) {
                return (
                  <div key={menu.label} className="rigpa-mega-menu-mobile-section">
                    <a
                      href={menu.url || "#"}
                      className="rigpa-mega-menu-mobile-section-btn"
                    >
                      <span className="rigpa-mega-menu-mobile-section-label">
                        {menu.label}
                      </span>
                    </a>
                  </div>
                );
              }

              return (
                <div
                  key={menu.label}
                  className="rigpa-mega-menu-mobile-section"
                >
                  <button
                    type="button"
                    className="rigpa-mega-menu-mobile-section-btn"
                    aria-expanded={isExpanded}
                    aria-controls={bodyId}
                    onClick={() =>
                      setMobileExpandedIndex(isExpanded ? null : index)
                    }
                  >
                    <span className="rigpa-mega-menu-mobile-section-label">
                      {menu.label}
                    </span>
                    <svg
                      className={`rigpa-mega-menu-mobile-chevron ${
                        isExpanded ? "rigpa-mega-menu-mobile-chevron--open" : ""
                      }`}
                      width="12"
                      height="12"
                      viewBox="0 0 24 24"
                      fill="none"
                      stroke="currentColor"
                      strokeWidth="2.5"
                      aria-hidden="true"
                    >
                      <path strokeLinecap="round" strokeLinejoin="round" d="M6 9l6 6 6-6" />
                    </svg>
                  </button>

                  <div
                    className={`rigpa-mega-menu-mobile-accordion ${
                      isExpanded ? "rigpa-mega-menu-mobile-accordion--open" : ""
                    }`}
                    role="region"
                    id={bodyId}
                  >
                    <div className="rigpa-mega-menu-mobile-accordion-inner">
                      <MobileSectionLinks menu={menu} id={`${bodyId}-links`} />
                    </div>
                  </div>
                </div>
              );
            })}
          </nav>
        </div>
      )}
    </div>
  );
}
