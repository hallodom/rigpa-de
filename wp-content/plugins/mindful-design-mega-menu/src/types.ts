export interface MenuItem {
  title: string;
  description: string;
  url: string;
}

export interface FeaturedCard {
  title: string;
  description: string;
  image: string;
  url: string;
}

export interface MenuSection {
  label: string;
  url?: string;
  items: MenuItem[];
  featured?: FeaturedCard;
  featuredCentres?: FeaturedCard[];
}

export interface MDMegaMenuConfig {
  assetsUrl: string;
  menus: MenuSection[];
  labels: {
    menu: string;
    openMenu: string;
    closeMenu: string;
    learnMore: string;
  };
  transparent?: boolean;
  menuTextColor?: string;
}

declare global {
  interface Window {
    mdMegaMenu?: MDMegaMenuConfig;
  }
}
