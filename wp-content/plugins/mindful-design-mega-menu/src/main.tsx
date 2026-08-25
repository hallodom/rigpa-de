import { StrictMode } from "react";
import { createRoot } from "react-dom/client";
import MegaMenuHeader from "./MegaMenuHeader";
import "./index.css";
import type { MDMegaMenuConfig } from "./types";

function getMountNodes(): HTMLElement[] {
  const nodes: HTMLElement[] = [];

  const byId = document.getElementById("md-mega-menu-root");
  if (byId) {
    nodes.push(byId);
  }

  document.querySelectorAll<HTMLElement>(".md-mega-menu-root[id]").forEach((el) => {
    if (!nodes.includes(el)) {
      nodes.push(el);
    }
  });

  document.querySelectorAll<HTMLElement>(".md-mega-menu-wrapper[id]").forEach((el) => {
    if (!nodes.includes(el)) {
      nodes.push(el);
    }
  });

  return nodes;
}

function applyMenuTextColor(node: HTMLElement, color?: string) {
  if (!color) {
    return;
  }
  // A per-instance value emitted via inline style on the wrapper (e.g. a
  // shortcode attr or per-page meta override) takes precedence over the
  // localized global default.
  if (node.style.getPropertyValue("--md-mega-menu-item-color")) {
    return;
  }
  node.style.setProperty("--md-mega-menu-item-color", color);
}

function mountMegaMenu(node: HTMLElement, config: MDMegaMenuConfig) {
  if (node.dataset.mdMegaMenuMounted === "true") {
    return;
  }

  node.dataset.mdMegaMenuMounted = "true";
  applyMenuTextColor(node, config.menuTextColor);

  createRoot(node).render(
    <StrictMode>
      <MegaMenuHeader
        menus={config.menus}
        labels={config.labels}
        transparent={config.transparent !== false}
      />
    </StrictMode>
  );
}

function initMDMegaMenu() {
  const config = window.mdMegaMenu;

  if (!config?.menus?.length) {
    return;
  }

  getMountNodes().forEach((node) => mountMegaMenu(node, config));
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initMDMegaMenu);
} else {
  initMDMegaMenu();
}
