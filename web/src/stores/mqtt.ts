import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { mqttService, type MqttMessage, type MqttConfig } from '/@/utils/mqtt'
// import { DJIoperations } from '/@/utils/mqttSdk'
import { baTableApi } from '/@/api/common'
import { ElNotification } from 'element-plus'
import { useRouter } from 'vue-router'
import { disposition } from '/@/config/disposition'
import { useShipLanes } from './shipLanes'
import { storeToRefs } from 'pinia'

export const useMqttStore = defineStore(
    'mqtt',
    () => {
        const router = useRouter()
        // 状态
        const error = ref<string | null>(null)
        const messages = ref<any>({})
        const subscribedTopics = ref<string[]>([])
        // 当前设备sn
        const gateway_sn = ref<any>('')
        // 设备osd
        const deviceOsds = ref<any>({})
        // 无人机osd
        const droneOsds = ref<any>({})
        // 所有设备
        const allDevice = ref<any>({})

        // 是否有正在执行的任务
        const isExecutingTask = ref(false)

        // 当前无人机sn
        const drone_sn = computed(() => {
            if (gateway_sn.value && deviceOsds.value[gateway_sn.value].sub_device) {
                return deviceOsds.value[gateway_sn.value].sub_device.device_sn
            }
            return ''
        })
        // 当前无人机状态
        const droneData = computed(() => {
            return droneOsds.value[drone_sn.value] || {}
        })
        // 当前设备状态
        const deviceData = computed(() => {
            return deviceOsds.value[gateway_sn.value] || {}
        })
        // 计算属性
        const connectionStatus = computed(() => {
            return mqttService.getConnected() ? 'connected' : 'disconnected'
        })
        // 计算设备数量
        const deviceCount = computed(() => {
            return Object.keys(deviceOsds.value).length
        })
        // 计算执行中设备数量
        const executingDeviceCount = computed(() => {
            return Object.keys(deviceOsds.value).filter((sn) => deviceOsds.value[sn].mode_code !== 0).length || 0
        })
        // 计算空闲中设备数量
        const idleDeviceCount = computed(() => {
            return Object.keys(deviceOsds.value).filter((sn) => deviceOsds.value[sn].mode_code === 0).length || 0
        })

        // 计算飞行器在线数量
        const droneCount = computed(() => {
            return (
                Object.keys(deviceOsds.value).filter((sn) => {
                    if (deviceOsds.value[sn].sub_device) {
                        return deviceOsds.value[sn].sub_device.device_sn
                    }
                    return false
                }).length || 0
            )
        })

        // 计算飞行器空闲数量
        const idleDroneCount = computed(() => {
            return Object.keys(deviceOsds.value).filter((sn) => deviceOsds.value[sn].drone_in_dock === 1).length || 0
        })

        // 计算飞行器执行中数量
        const executingDroneCount = computed(() => {
            return Object.keys(deviceOsds.value).filter((sn) => deviceOsds.value[sn].drone_in_dock === 0).length || 0
        })

        const isConnected = computed(() => mqttService.getConnected())

        const hasError = computed(() => !!error.value)

        // 连接MQTT
        const connect = async (config?: Partial<MqttConfig>): Promise<boolean> => {
            error.value = null

            try {
                const success = await mqttService.connect()
                if (!success) {
                    error.value = '连接失败'
                }
                return success
            } catch (err) {
                error.value = err instanceof Error ? err.message : '连接错误'
                return false
            }
        }

        // 断开连接
        const disconnect = () => {
            mqttService.disconnect()
            subscribedTopics.value = []
            gateway_sn.value = ''
            deviceOsds.value = {}
        }

        const resetIsExecutingTask = () => {
            isExecutingTask.value = false
        }

        // 获取任务计划
        const getTaskPlan = async () => {
            const taskPlanApi = new baTableApi('/admin/Flighttask/')
            const res = await taskPlanApi.index()
            // 判断是否有执行中的任务
            const executingTask = res.data.list.filter((item: any) => item.status === 'in_progress')
            console.log('executingTask', executingTask)
            if (executingTask.length == 0) {
                return
            }
            // 如果有执行中的任务，则跳转过去
            if (executingTask.length > 0) {
                const shipLanesStore = useShipLanes()
                ElNotification({
                    title: '提示',
                    message: '有执行中的任务，为您跳转过去',
                    duration: 3000,
                    type: 'warning',
                })
                // 匹配在哪个设备执行
                const executingDevice = allDevice.value.find((item: any) => item.id === executingTask[0].equipment_id)
                // 匹配到哪个航线
                const airlineApi = new baTableApi('/admin/Airline/')
                const res = await airlineApi.edit({ id: executingTask[0].airline_id })
                const airlineRes = res.data.row
                const kmzJson = JSON.parse(airlineRes.kmz_json)
                shipLanesStore.showShipForm(kmzJson)
                // 拿到设备的sn
                const executingDeviceSn = executingDevice.sn
                // 更改当先设备sn
                gateway_sn.value = executingDeviceSn
                isExecutingTask.value = true
                setTimeout(() => {
                    console.log('isExecutingTask', isExecutingTask.value)
                    // 更改直播的sn
                    disposition.djiDock.gateway_sn = executingDeviceSn
                    disposition.device.device_sn = drone_sn.value
                    // 跳转到飞行空间
                    router.push(`/admin/flightSpace?id=${executingDevice.project_id}`)
                }, 2000)
            }
        }

        // 获取所有设备
        const getDeviceList = async () => {
            const equipmentApi = new baTableApi('/admin/Equipment/')
            const res = await equipmentApi.index()
            console.log(res.data.list)
            allDevice.value = res.data.list
            res.data.list.forEach((item: any) => {
                deviceOsds.value[item.sn] = {}
            })
            // 订阅所有设备的osd
            subscribeDeviceOsd()
        }

        // 获取无人机osd
        const getDroneOsd = async () => {
            droneOsds.value = {}
            for (const sn in deviceOsds.value) {
                if (deviceOsds.value[sn].sub_device) {
                    droneOsds.value[deviceOsds.value[sn].sub_device.device_sn] = {}
                    subscribe(`thing/product/${deviceOsds.value[sn].sub_device.device_sn}/osd`, 0)
                }
            }
        }

        // 订阅所有设备的osd
        const subscribeDeviceOsd = async () => {
            for (const sn in deviceOsds.value) {
                // 订阅设备osd
                subscribe(`thing/product/${sn}/osd`, 0)
                // 订阅设备状态
                // subscribe(`thing/product/${sn}/state`, 0)
            }
            setTimeout(() => {
                getDroneOsd()
            }, 3000)
        }

        // 取消订阅所有设备的osd
        const unsubscribeDeviceOsd = async () => {
            for (const sn in deviceOsds.value) {
                // 取消订阅设备osd
                unsubscribe(`thing/product/${sn}/osd`)
                if (deviceOsds.value[sn].sub_device) {
                    unsubscribe(`thing/product/${deviceOsds.value[sn].sub_device.device_sn}/osd`)
                }
                // 取消订阅设备状态
                // unsubscribe(`thing/product/${sn}/state`)
            }
        }

        // 订阅主题
        const subscribe = async (topic: string, qos: number = 0): Promise<boolean> => {
            if (!isConnected.value) {
                error.value = '未连接到MQTT服务器'
                return false
            }

            const success = await mqttService.subscribe(topic, qos)
            if (success) {
                subscribedTopics.value.push(topic)
            } else {
                error.value = `订阅主题失败: ${topic}`
            }
            return success
        }

        // 取消订阅
        const unsubscribe = async (topic: string): Promise<boolean> => {
            if (!isConnected.value) {
                error.value = '未连接到MQTT服务器'
                return false
            }

            const success = await mqttService.unsubscribe(topic)
            if (success) {
                const index = subscribedTopics.value.indexOf(topic)
                if (index > -1) {
                    subscribedTopics.value.splice(index, 1)
                }
            } else {
                error.value = `取消订阅失败: ${topic}`
            }
            return success
        }

        // 发布消息
        const publish = async (topic: string, message: string | Buffer, qos: number = 0, retain: boolean = false): Promise<boolean> => {
            if (!isConnected.value) {
                error.value = '未连接到MQTT服务器'
                return false
            }

            const success = await mqttService.publish(topic, message, qos, retain)
            if (!success) {
                error.value = `发布消息失败: ${topic}`
            }
            return success
        }

        // 消息处理器
        const handleMessage = (message: MqttMessage) => {
            if (message.topic.includes('osd')) {
                const sn = message.topic.split('/')[2]
                if (deviceOsds.value[sn]) {
                    deviceOsds.value[sn] = {
                        ...deviceOsds.value[sn],
                        ...JSON.parse(message.payload.toString()).data,
                    }
                }
                if (droneOsds.value[sn]) {
                    droneOsds.value[sn] = {
                        ...droneOsds.value[sn],
                        ...JSON.parse(message.payload.toString()).data,
                    }
                }
            } else {
                messages.value = message
            }
            // 保持最近100条消息
            // if (messages.value.length > 10) {
            //     messages.value = messages.value.slice(-10)
            // }
        }

        // 清空消息
        const clearMessages = () => {
            messages.value = []
        }
        // 清空错误
        const clearError = () => {
            error.value = null
        }
        // 获取特定主题的消息
        const getMessagesByTopic = (topic: string) => {
            if (messages.value.topic === topic) {
                return messages.value
            }
            return {}
        }
        // 初始化MQTT连接
        const initMqtt = async () => {
            isExecutingTask.value = false
            console.log('🚀 初始化MQTT...')
            try {
                // 设置消息处理器
                mqttService.onMessage('#', handleMessage)
                // 自动连接
                const success = await connect()
                if (success) {
                    console.log('✅ MQTT初始化成功')
                } else {
                    console.error('❌ MQTT初始化失败')
                }
            } catch (error) {
                console.error('💥 MQTT初始化异常:', error)
            }
        }

        return {
            // 状态
            error,
            messages,
            subscribedTopics,
            gateway_sn,
            allDevice,
            deviceOsds,
            droneOsds,
            // 计算属性
            connectionStatus,
            isConnected,
            hasError,
            // 设备
            deviceData,
            deviceCount,
            executingDeviceCount,
            idleDeviceCount,
            // 飞行器
            droneCount,
            drone_sn,
            droneData,
            idleDroneCount,
            executingDroneCount,
            // 方法
            connect,
            disconnect,
            subscribe,
            unsubscribe,
            publish,
            clearMessages,
            clearError,
            getMessagesByTopic,
            initMqtt,
            subscribeDeviceOsd,
            unsubscribeDeviceOsd,
            getDeviceList,
            getDroneOsd,
            getTaskPlan,
            isExecutingTask,
            resetIsExecutingTask,
        }
    },
    {
        persist: {
            key: 'mqtt-store',
            storage: localStorage,
        },
    }
)
