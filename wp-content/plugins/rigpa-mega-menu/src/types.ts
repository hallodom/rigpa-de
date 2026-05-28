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
}

export interface RigpaMegaMenuConfig {
  assetsUrl: string;
  lang: "english" | "german";
  menus: MenuSection[];
  transparent?: boolean;
  menuTextColor?: string;
}

declare global {
  interface Window {
    rigpaMegaMenu?: RigpaMegaMenuConfig;
  }
}
