require('./bootstrap');

require('alpinejs');

window.Vue = require('vue').default;
import VModal from 'vue-js-modal'

Vue.use(VModal);

Vue.component(
    "theme-switcher",
    require("./components/ThemeSwitcher.vue").default
);

Vue.component(
    "new-project-modal",
    require("./components/NewProjectModal.vue").default
);

const app = new Vue({
    el: '#app',
});