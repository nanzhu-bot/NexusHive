// directives/draggable.ts
import type { Directive } from 'vue'

// 扩展HTMLElement类型以支持清理函数
declare global {
    interface HTMLElement {
        _cleanup?: () => void
    }
}

interface DragState {
    isDragging: boolean
    startX: number
    startY: number
    initialLeft: number
    initialTop: number
    initialTransform: string
}

const draggable: Directive = {
    mounted(el: HTMLElement) {
        const state: DragState = {
            isDragging: false,
            startX: 0,
            startY: 0,
            initialLeft: 0,
            initialTop: 0,
            initialTransform: '',
        }

        // 确保元素有绝对定位
        if (getComputedStyle(el).position === 'static') {
            el.style.position = 'absolute'
        }

        // 设置基础样式
        el.style.userSelect = 'none'
        el.style.touchAction = 'none'

        // 创建一个拖拽区域元素
        const dragHandle = document.createElement('div')
        dragHandle.style.position = 'absolute'
        dragHandle.style.top = '0'
        dragHandle.style.left = '0'
        dragHandle.style.width = '100%'
        dragHandle.style.height = '40px'
        dragHandle.style.cursor = 'move'
        dragHandle.style.zIndex = '9999'
        dragHandle.style.pointerEvents = 'auto'

        // 将拖拽区域添加到元素中
        el.appendChild(dragHandle)

        // 设置元素其他区域的鼠标样式
        el.style.cursor = 'default'

        // 获取元素的初始位置
        const getElementPosition = () => {
            const computedStyle = getComputedStyle(el)

            // 获取当前的 left 和 top 值
            let left = parseInt(computedStyle.left) || 0
            let top = parseInt(computedStyle.top) || 0

            // 如果元素使用了 transform 居中，需要计算实际位置
            if (computedStyle.transform && computedStyle.transform !== 'none') {
                const rect = el.getBoundingClientRect()
                left = rect.left
                top = rect.top
            }

            const transform = computedStyle.transform

            return { left, top, transform }
        }

        // 鼠标按下事件
        const handleMouseDown = (e: MouseEvent) => {
            // 检查事件是否来自拖拽区域
            if (!dragHandle.contains(e.target as Node)) {
                return // 不在拖拽区域内，不触发拖拽
            }

            e.preventDefault()
            e.stopPropagation()

            state.isDragging = true
            state.startX = e.clientX
            state.startY = e.clientY

            const position = getElementPosition()
            state.initialLeft = position.left
            state.initialTop = position.top
            state.initialTransform = position.transform

            // 立即移除 transform 居中样式，避免位置跳跃
            if (position.transform && position.transform !== 'none') {
                el.style.transform = 'none'
                // 重新计算位置
                const newPosition = getElementPosition()
                state.initialLeft = newPosition.left
                state.initialTop = newPosition.top
            }

            // 添加事件监听
            document.addEventListener('mousemove', handleDragMouseMove)
            document.addEventListener('mouseup', handleMouseUp)
        }

        // 拖拽时的鼠标移动事件
        const handleDragMouseMove = (e: MouseEvent) => {
            if (!state.isDragging) return

            e.preventDefault()

            const deltaX = e.clientX - state.startX
            const deltaY = e.clientY - state.startY

            // 计算新位置
            const newLeft = state.initialLeft + deltaX
            const newTop = state.initialTop + deltaY

            // 边界检查 - 防止拖出屏幕
            const maxX = window.innerWidth - el.offsetWidth
            const maxY = window.innerHeight - el.offsetHeight

            const boundedLeft = Math.max(0, Math.min(newLeft, maxX))
            const boundedTop = Math.max(0, Math.min(newTop, maxY))

            // 应用新位置
            el.style.left = `${boundedLeft}px`
            el.style.top = `${boundedTop}px`
        }

        // 鼠标释放事件
        const handleMouseUp = () => {
            if (!state.isDragging) return

            state.isDragging = false

            // 移除事件监听
            document.removeEventListener('mousemove', handleDragMouseMove)
            document.removeEventListener('mouseup', handleMouseUp)
        }

        // 触摸事件支持
        const handleTouchStart = (e: TouchEvent) => {
            if (e.touches.length !== 1) return

            const touch = e.touches[0]

            // 检查触摸事件是否来自拖拽区域
            if (!dragHandle.contains(e.target as Node)) {
                return // 不在拖拽区域内，不触发拖拽
            }

            e.preventDefault()
            e.stopPropagation()

            state.isDragging = true
            state.startX = touch.clientX
            state.startY = touch.clientY

            const position = getElementPosition()
            state.initialLeft = position.left
            state.initialTop = position.top
            state.initialTransform = position.transform

            // 立即移除 transform 居中样式，避免位置跳跃
            if (position.transform && position.transform !== 'none') {
                el.style.transform = 'none'
                // 重新计算位置
                const newPosition = getElementPosition()
                state.initialLeft = newPosition.left
                state.initialTop = newPosition.top
            }

            el.style.zIndex = '100'

            document.addEventListener('touchmove', handleTouchMove, { passive: false })
            document.addEventListener('touchend', handleTouchEnd)
        }

        const handleTouchMove = (e: TouchEvent) => {
            if (!state.isDragging || e.touches.length !== 1) return

            e.preventDefault()

            const touch = e.touches[0]
            const deltaX = touch.clientX - state.startX
            const deltaY = touch.clientY - state.startY

            const newLeft = state.initialLeft + deltaX
            const newTop = state.initialTop + deltaY

            const maxX = window.innerWidth - el.offsetWidth
            const maxY = window.innerHeight - el.offsetHeight

            const boundedLeft = Math.max(0, Math.min(newLeft, maxX))
            const boundedTop = Math.max(0, Math.min(newTop, maxY))

            el.style.left = `${boundedLeft}px`
            el.style.top = `${boundedTop}px`
        }

        const handleTouchEnd = () => {
            if (!state.isDragging) return

            state.isDragging = false
            el.style.zIndex = '100'

            document.removeEventListener('touchmove', handleTouchMove)
            document.removeEventListener('touchend', handleTouchEnd)
        }

        // 绑定事件
        el.addEventListener('mousedown', handleMouseDown)
        el.addEventListener('touchstart', handleTouchStart, { passive: false })

        // 清理函数
        const cleanup = () => {
            el.removeEventListener('mousedown', handleMouseDown)
            el.removeEventListener('touchstart', handleTouchStart)
            document.removeEventListener('mousemove', handleDragMouseMove)
            document.removeEventListener('mouseup', handleMouseUp)
            document.removeEventListener('touchmove', handleTouchMove)
            document.removeEventListener('touchend', handleTouchEnd)

            // 移除拖拽区域元素
            if (dragHandle.parentNode) {
                dragHandle.parentNode.removeChild(dragHandle)
            }
        }

        // 在组件卸载时清理事件监听
        el._cleanup = cleanup
    },

    unmounted(el: HTMLElement) {
        if (el._cleanup) {
            el._cleanup()
        }
    },
}

export default draggable
