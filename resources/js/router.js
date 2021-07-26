import Vue from "vue";
import VueRouter from "vue-router";
import HomePage from "./components/pages/HomePage";
import AdminPage from "./components/pages/AdminPage";

Vue.use(VueRouter)
export default new VueRouter({
    routes: [
        { path: "/", name:"home", component: HomePage },
        { path: "/admin", name: "admin", component: AdminPage,}
    ],
    scrollBehavior (to, from, savedPosition) {
        return { x: 0, y: 0 }
    },
    mode: "history"
})
