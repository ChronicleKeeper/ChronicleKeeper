/**
 * Workaround for Tabler Bug https://github.com/tabler/tabler/issues/1865
 *
 * The bug occurs because in the confirm dialog the bootstrap modal loading would conflict with tabler js
 * so we render the needed bootstrap js ourselves because most of the js from tabler is not a hard requirement
 */
import {
    Alert,
    Button,
    Carousel,
    Collapse,
    Dropdown,
    Modal,
    Offcanvas,
    Popover,
    ScrollSpy,
    Tab,
    Toast,
    Tooltip,
} from "bootstrap";

// Initialize Bootstrap components
const initializeComponent = (selector, Component) => {
    document.querySelectorAll(selector).forEach((element) => {
        try {
            if (Component.name === "Tooltip" || Component.name === "Popover") {
                // For Tooltip and Popover, pass options if necessary
                const options = {}; // Add any custom options here if needed
                new Component(element, options);
            } else {
                // Default initialization for other components
                new Component(element);
            }
        } catch (error) {
            console.error(`Failed to initialize ${Component.name}:`, error);
        }
    });
};

// Map of components and their selectors
const components = {
    '[data-bs-toggle="alert"]': Alert,
    '[data-bs-toggle="button"]': Button,
    '[data-bs-toggle="carousel"]': Carousel,
    '[data-bs-toggle="collapse"]': Collapse,
    '[data-bs-toggle="dropdown"]': Dropdown,
    '[data-bs-toggle="modal"]': Modal,
    '[data-bs-toggle="offcanvas"]': Offcanvas,
    '[data-bs-toggle="popover"]': Popover,
    '[data-bs-toggle="scrollspy"]': ScrollSpy,
    '[data-bs-toggle="tab"]': Tab,
    '[data-bs-toggle="toast"]': Toast,
    '[data-bs-toggle="tooltip"]': Tooltip,
};

const install = (app) => {
    // Initialize all components after Vue app is mounted
    app.mixin({
        mounted() {
            Object.entries(components).forEach(([selector, Component]) => {
                initializeComponent(selector, Component);
            });

        },
    });
};

// Export components
export {
    Alert,
    Button,
    Carousel,
    Collapse,
    Dropdown,
    Modal,
    Offcanvas,
    Popover,
    ScrollSpy,
    Tab,
    Toast,
    Tooltip,
};

export default {
    Alert,
    Button,
    Carousel,
    Collapse,
    Dropdown,
    Modal,
    Offcanvas,
    Popover,
    ScrollSpy,
    Tab,
    Toast,
    Tooltip,
};
