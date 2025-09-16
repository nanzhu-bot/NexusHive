import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import { loadLang } from '/@/lang/index'
import { registerIcons } from '/@/utils/common'
import ElementPlus from 'element-plus'
import mitt from 'mitt'
import pinia from '/@/stores/index'
import { directives } from '/@/utils/directives'
import 'element-plus/dist/index.css'
import 'element-plus/theme-chalk/display.css'
import 'font-awesome/css/font-awesome.min.css'
import '/@/styles/index.scss'

// 引入css
import 'mars3d-cesium/Build/Cesium/Widgets/widgets.css'
import 'mars3d/mars3d.css' // v3.8.6及之前版本使用 import "mars3d/dist/mars3d.css";
import 'mars3d-space'
// modules import mark, Please do not remove.

async function start() {
    const app = createApp(App)
    app.use(pinia)

    // 全局语言包加载
    await loadLang(app)

    app.use(router)
    app.use(ElementPlus)

    // 全局注册
    directives(app) // 指令
    registerIcons(app) // icons

    // 初始化MQTT连接 - 在应用挂载前初始化
    // try {
    // } catch (error) {
    //     console.error('❌ MQTT初始化失败:', error)
    // }

    app.mount('#app')

    // modules start mark, Please do not remove.

    app.config.globalProperties.eventBus = mitt()
}
start()
