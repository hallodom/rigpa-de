import { useCallback, useEffect, useId, useRef, useState } from "react";
import type { MenuSection } from "./types";

interface MegaMenuHeaderProps {
  menus: MenuSection[];
  lang: "english" | "german";
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
      className={`rigpa-mega-menu-panel rigpamm:grid rigpamm:gap-0 rigpamm:bg-white rigpamm:border rigpamm:border-neutral-200 rigpamm:rounded-lg rigpamm:shadow-xl rigpamm:overflow-hidden ${
        menu.featured ? "rigpamm:grid-cols-[1fr_300px]" : "rigpamm:grid-cols-1"
      }`}
    >
      <div className="rigpamm:p-8 rigpamm:grid rigpamm:grid-cols-2 rigpamm:gap-x-8 rigpamm:gap-y-6">
        {menu.items.map((item, idx) => (
          <a key={idx} href={item.url} className="rigpamm:group rigpamm:block">
            <div className="rigpamm:font-medium rigpamm:text-sm rigpamm:mb-1 rigpamm:group-hover:text-neutral-600 rigpamm:transition-colors">
              {item.title}
            </div>
            <div className="rigpamm:text-xs rigpamm:text-neutral-500 rigpamm:leading-relaxed">
              {item.description}
            </div>
          </a>
        ))}
      </div>

      {menu.featured && (
        <div className="rigpamm:bg-neutral-50 rigpamm:p-6 rigpamm:flex rigpamm:flex-col rigpamm:border-l rigpamm:border-neutral-200">
          <div className="rigpamm:aspect-[4/3] rigpamm:rounded rigpamm:overflow-hidden rigpamm:mb-4">
            <img
              src={menu.featured.image}
              alt={menu.featured.title}
              className="rigpamm:w-full rigpamm:h-full rigpamm:object-cover"
            />
          </div>
          <div className="rigpamm:font-medium rigpamm:text-sm rigpamm:mb-2">
            {menu.featured.title}
          </div>
          <div className="rigpamm:text-xs rigpamm:text-neutral-600 rigpamm:mb-4 rigpamm:leading-relaxed">
            {menu.featured.description}
          </div>
          <a
            href={menu.featured.url}
            className="rigpamm:text-xs rigpamm:font-medium rigpamm:hover:underline rigpamm:mt-auto"
          >
            {learnMore}
          </a>
        </div>
      )}
    </div>
  );
}

export default function MegaMenuHeader({ menus, lang }: MegaMenuHeaderProps) {
  const baseId = useId().replace(/:/g, "");
  const rootRef = useRef<HTMLDivElement>(null);
  const hoverTimeoutRef = useRef<ReturnType<typeof setTimeout> | null>(null);
  const isDesktop = useMediaQuery("(min-width: 768px)");

  const [activeIndex, setActiveIndex] = useState<number | null>(null);
  const [renderedIndex, setRenderedIndex] = useState<number | null>(null);
  const [panelVisible, setPanelVisible] = useState(false);
  const [mobileOpen, setMobileOpen] = useState(false);
  const [mobileRendered, setMobileRendered] = useState(false);
  const [mobileVisible, setMobileVisible] = useState(false);
  const [mobileExpandedIndex, setMobileExpandedIndex] = useState<number | null>(null);

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
      setActiveIndex(index);
    },
    [clearHoverTimeout, isDesktop]
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

  return (
    <div
      ref={rootRef}
      className="rigpa-mega-menu-header rigpamm:relative rigpamm:w-full rigpamm:bg-white rigpamm:border-b rigpamm:border-neutral-200 rigpamm:z-50"
    >
      <div className="rigpamm:mx-auto rigpamm:max-w-7xl rigpamm:px-6">
        <div className="rigpamm:flex rigpamm:items-center rigpamm:justify-between rigpamm:min-h-[56px]">
          {isDesktop ? (
            <nav
              className="rigpamm:flex rigpamm:flex-wrap rigpamm:gap-1"
              aria-label={menuLabel}
              onMouseLeave={scheduleCloseDesktopPanel}
            >
              {menus.map((menu, index) => {
                const panelId = `${baseId}-panel-${index}`;
                const isOpen = activeIndex === index;

                return (
                  <div key={menu.label} className="rigpamm:relative">
                    <button
                      type="button"
                      className={`rigpamm:px-4 rigpamm:py-4 rigpamm:text-sm rigpamm:font-medium rigpamm:transition-colors rigpamm:bg-transparent rigpamm:border-0 rigpamm:cursor-pointer ${
                        isOpen
                          ? "rigpamm:text-neutral-900"
                          : "rigpamm:text-neutral-700 rigpamm:hover:text-neutral-900"
                      }`}
                      aria-expanded={isOpen}
                      aria-controls={panelId}
                      aria-haspopup="true"
                      onMouseEnter={() => openDesktopPanel(index)}
                      onFocus={() => openDesktopPanel(index)}
                      onClick={() =>
                        setActiveIndex(isOpen ? null : index)
                      }
                    >
                      {menu.label}
                    </button>
                  </div>
                );
              })}
            </nav>
          ) : (
            <button
              type="button"
              className="rigpamm:flex rigpamm:items-center rigpamm:gap-2 rigpamm:px-3 rigpamm:py-3 rigpamm:text-sm rigpamm:font-medium rigpamm:bg-transparent rigpamm:border-0 rigpamm:cursor-pointer rigpamm:text-neutral-900"
              aria-expanded={mobileOpen}
              aria-controls={`${baseId}-mobile-menu`}
              onClick={() => {
                setMobileOpen((open) => !open);
                setMobileExpandedIndex(null);
              }}
            >
              <span className="rigpamm:sr-only">
                {mobileOpen ? closeMenuLabel : openMenuLabel}
              </span>
              <svg
                className="rigpamm:w-5 rigpamm:h-5"
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
              <span>{menuLabel}</span>
            </button>
          )}
        </div>
      </div>

      {isDesktop && renderedIndex !== null && (
        <div
          className={`rigpa-mega-menu-dropdown rigpamm:absolute rigpamm:left-0 rigpamm:right-0 rigpamm:top-full ${
            panelVisible ? "rigpa-mega-menu-dropdown--open" : ""
          }`}
          onMouseEnter={clearHoverTimeout}
          onMouseLeave={scheduleCloseDesktopPanel}
        >
          <div className="rigpamm:mx-auto rigpamm:max-w-4xl rigpamm:px-6 rigpamm:py-6">
            <MenuPanel
              menu={menus[renderedIndex]}
              lang={lang}
              panelId={`${baseId}-panel-${renderedIndex}`}
            />
          </div>
        </div>
      )}

      {!isDesktop && mobileRendered && (
        <div
          id={`${baseId}-mobile-menu`}
          className={`rigpa-mega-menu-mobile-panel rigpamm:border-t rigpamm:border-neutral-200 rigpamm:bg-white ${
            mobileVisible ? "rigpa-mega-menu-mobile-panel--open" : ""
          }`}
        >
          <div className="rigpamm:mx-auto rigpamm:max-w-7xl rigpamm:px-6 rigpamm:py-4 rigpamm:space-y-2">
            {menus.map((menu, index) => {
              const panelId = `${baseId}-mobile-panel-${index}`;
              const isExpanded = mobileExpandedIndex === index;

              return (
                <div
                  key={menu.label}
                  className="rigpamm:border rigpamm:border-neutral-200 rigpamm:rounded-lg rigpamm:overflow-hidden"
                >
                  <button
                    type="button"
                    className="rigpamm:w-full rigpamm:flex rigpamm:items-center rigpamm:justify-between rigpamm:px-4 rigpamm:py-3 rigpamm:text-sm rigpamm:font-medium rigpamm:text-left rigpamm:bg-neutral-50 rigpamm:border-0 rigpamm:cursor-pointer"
                    aria-expanded={isExpanded}
                    aria-controls={panelId}
                    onClick={() =>
                      setMobileExpandedIndex(isExpanded ? null : index)
                    }
                  >
                    <span className="rigpamm:font-serif">{menu.label}</span>
                    <svg
                      className={`rigpamm:w-4 rigpamm:h-4 rigpamm:transition-transform ${
                        isExpanded ? "rigpamm:rotate-180" : ""
                      }`}
                      viewBox="0 0 24 24"
                      fill="none"
                      stroke="currentColor"
                      strokeWidth="2"
                      aria-hidden="true"
                    >
                      <path strokeLinecap="round" d="M6 9l6 6 6-6" />
                    </svg>
                  </button>

                  {isExpanded && (
                    <div id={panelId} className="rigpamm:p-4 rigpamm:bg-white">
                      <MenuPanel
                        menu={menu}
                        lang={lang}
                        panelId={panelId}
                      />
                    </div>
                  )}
                </div>
              );
            })}
          </div>
        </div>
      )}
    </div>
  );
}
