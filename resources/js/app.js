/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require("./bootstrap");

window.Vue = require("vue");

import { Form, HasError, AlertError } from "vform";
import VueRouter from "vue-router";
import moment from "moment";
import VueProgressBar from "vue-progressbar";
import swal from "sweetalert2";

// Gate
import Gate from "./Gate";
Vue.prototype.$gate = new Gate(window.user);

// Custom event
window.Fire = new Vue();

// Sweetalert
window.swal = swal;
const toast = swal.mixin({
    toast: true,
    position: "top-end",
    showConfirmButton: false,
    timer: 3000
});
window.toast = toast;

// Progress bar
Vue.use(VueProgressBar, {
    color: "rgb(143, 255, 199)",
    failedColor: "red",
    height: "3px"
});

// Form validation
window.Form = Form;
Vue.component(HasError.name, HasError);
Vue.component(AlertError.name, AlertError);

// Vue router
Vue.use(VueRouter);
let routes = [
    {
        path: "/dashboard",
        component: require("./components/Dashboard.vue").default
    },
    {
        path: "/profile",
        component: require("./components/Profile.vue").default
    },
    {
        path: "/users",
        component: require("./components/Users.vue").default
    },
    {
        path: "/developer",
        component: require("./components/Developer.vue").default
    },
    {
        path: "*",
        component: require("./components/NotFound.vue").default
    }
];
const router = new VueRouter({
    mode: "history",
    routes // short for `routes: routes`
});

// Global filter
Vue.filter("upperText", text => {
    return text.charAt(0).toUpperCase() + text.slice(1);
});
Vue.filter("humanDate", date => {
    return moment(date).format("D MMMM YYYY");
});

// Example component
Vue.component(
    "example-component",
    require("./components/ExampleComponent.vue").default
);

// Laravel Vue Pagination
Vue.component("pagination", require("laravel-vue-pagination"));

// Laravel Passport
Vue.component(
    "passport-clients",
    require("./components/passport/Clients.vue").default
);
Vue.component(
    "passport-authorized-clients",
    require("./components/passport/AuthorizedClients.vue").default
);
Vue.component(
    "passport-personal-access-tokens",
    require("./components/passport/PersonalAccessTokens.vue").default
);

// 404 Component
Vue.component("not-found", require("./components/NotFound.vue").default);

const app = new Vue({
    el: "#app",
    router,
    data: {
        search: ""
    },
    methods: {
        searchit: _.debounce(() => {
            Fire.$emit("searching");
        }, 1000),
        printme() {
            window.print();
        }
    }
});
