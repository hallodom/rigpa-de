import { StrictMode } from "react";
import { createRoot } from "react-dom/client";
import MegaMenuHeader from "./MegaMenuHeader";
import "./index.css";
import type { RigpaMegaMenuConfig } from "./types";

function getMountNodes(): HTMLElement[] {
  const nodes: HTMLElement[] = [];

  const byId = document.getElementById("rigpa-mega-menu-root");
  if (byId) {
    nodes.push(byId);
  }

  document.querySelectorAll<HTMLElement>(".rigpa-mega-menu-root[id]").forEach((el) => {
    if (!nodes.includes(el)) {
      nodes.push(el);
    }
  });

  document.querySelectorAll<HTMLElement>(".rigpa-mega-menu-wrapper[id]").forEach((el) => {
    if (!nodes.includes(el)) {
      nodes.push(el);
    }
  });

  return nodes;
}

function mountMegaMenu(node: HTMLElement, config: RigpaMegaMenuConfig) {
  if (node.dataset.rigpaMegaMenuMounted === "true") {
    return;
  }

  node.dataset.rigpaMegaMenuMounted = "true";

  createRoot(node).render(
    <StrictMode>
      <MegaMenuHeader menus={config.menus} lang={config.lang} />
    </StrictMode>
  );
}

function initRigpaMegaMenu() {
  const config = window.rigpaMegaMenu;

  if (!config?.menus?.length) {
    return;
  }

  getMountNodes().forEach((node) => mountMegaMenu(node, config));
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initRigpaMegaMenu);
} else {
  initRigpaMegaMenu();
}
