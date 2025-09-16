<template>
    <div class="dashboard">
        <div class="dashboard-content">
            <!-- 底部 -->
            <div class="left">
                <!-- 无人机监控 -->
                <div class="aerodrome">
                    <div class="aerodrome-header">
                        <img src="/img/dashboard/wrj-icon.png" alt="" class="header-icon" />
                        <span>无人机监控</span>
                    </div>
                    <div class="aerodrome-content">
                        <div class="content-item">
                            <img src="/img/dashboard/wrj.png" alt="" class="content-item-icon" />
                            <div class="content-item-info">
                                <div class="info-name">无人机在线总数量</div>
                                <div class="info-value">{{ droneCount }}架</div>
                            </div>
                        </div>
                        <div class="content-item">
                            <div class="item-box">
                                <div class="info-name">执行中</div>
                                <div class="info-value">{{ executingDroneCount }}架</div>
                            </div>
                            <div class="item-box">
                                <div class="info-name">空闲中</div>
                                <div class="info-value">{{ idleDroneCount }}架</div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- 机场监控 -->
                <div class="aerodrome">
                    <div class="aerodrome-header">
                        <img src="/img/dashboard/jc-icon.png" alt="" class="header-icon" />
                        <span>机场监控</span>
                    </div>
                    <div class="aerodrome-content">
                        <div class="content-item">
                            <img src="/img/dashboard/jc.png" alt="" class="content-item-icon" />
                            <div class="content-item-info">
                                <div class="info-name">机场在线总数量</div>
                                <div class="info-value">{{ deviceCount }}架</div>
                            </div>
                        </div>
                        <div class="content-item">
                            <div class="item-box">
                                <div class="info-name">执行中</div>
                                <div class="info-value">{{ executingDeviceCount }}架</div>
                            </div>
                            <div class="item-box">
                                <div class="info-name">空闲中</div>
                                <div class="info-value">{{ idleDeviceCount }}架</div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- 人员统计 -->
                <!-- <div class="aerodrome">
                <div class="aerodrome-header">
                    <img src="/img/dashboard/ry.png" alt="" class="header-icon" />
                    <span>人员统计</span>
                </div>
                <div class="aerodrome-content">
                    <div class="content-item-ry">
                        <span class="item-name">飞手人数</span>
                        <span class="item-value">26人</span>
                    </div>
                    <div class="content-item-ry">
                        <span class="item-name">在飞人数</span>
                        <span class="item-value">5人</span>
                    </div>
                </div>
            </div> -->
                <!-- 历史数据统计 -->
                <div class="aerodrome">
                    <div class="aerodrome-header">
                        <img src="/img/dashboard/sj.png" alt="" class="header-icon" />
                        <span>历史数据统计</span>
                    </div>
                    <div class="aerodrome-content">
                        <div class="content-item">
                            <div class="item-box">
                                <div class="info-name">飞行总里程</div>
                                <div class="info-value">{{ info.total_flight_distance }}</div>
                            </div>
                            <div class="item-box">
                                <div class="info-name">飞行总时长</div>
                                <div class="info-value">{{ info.total_flight_time }}</div>
                            </div>
                        </div>
                        <div class="content-item">
                            <div class="item-box">
                                <div class="info-name">飞行总次数</div>
                                <div class="info-value">{{ info.total_flight_sorties }}次</div>
                            </div>
                            <div class="item-box">
                                <div class="info-name">航线总数量</div>
                                <div class="info-value">{{ info.total_airline }}个</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 地图 -->
        <Map :url="configUrl" :options="options" map-key="test" @onload="marsOnload" />
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import * as mars3d from 'mars3d'
import Map from '/@/views/backend/flightSpace/map/index.vue'
import { baTableApi } from '/@/api/common'
import { useMqttStore } from '/@/stores/mqtt'
import { ElNotification } from 'element-plus'
const api = new baTableApi('admin/Dashboard/osd/')

const mqttStore = useMqttStore()

// 设备
const deviceCount = computed(() => mqttStore.deviceCount)
const executingDeviceCount = computed(() => mqttStore.executingDeviceCount)
const idleDeviceCount = computed(() => mqttStore.idleDeviceCount)
// 飞行器
const droneCount = computed(() => mqttStore.droneCount)
const idleDroneCount = computed(() => mqttStore.idleDroneCount)
const executingDroneCount = computed(() => mqttStore.executingDroneCount)

// 设备列表接口
const equipmentApi = new baTableApi('/admin/Equipment/')

const configUrl = 'config/mapConfig.json'
const options = ref({
    scene: {
        center: { lat: 30.469187, lng: 105.5646, alt: 930.2, heading: 2.1, pitch: -55.2 },
    },
    globe: {
        depthTestAgainstTerrain: true,
    },
})

const info = ref<any>({
    total_airline: 0,
    total_flight_distance: '0',
    total_flight_sorties: 0,
    total_flight_time: '0',
})

const right_index = ref(0)

const marsOnload = (map: any) => {
    console.log('marsOnload', map)

    // console.log(map)
}

const equipmentList = ref<any>([])

// 获取设备列表
const getEquipmentList = async () => {
    await mqttStore.getDeviceList()
    if (Object.keys(mqttStore.deviceOsds).length == 0) {
        ElNotification({
            message: '暂未查询到设备，请在设备绑定后前往设备管理添加!',
            type: 'warning',
            duration: 3000,
        })
    }
}

// 获取信息
const getInfo = async () => {
    const res = await api.index()
    info.value = res.data
}

onMounted(() => {
    getInfo()
    getEquipmentList()
})
</script>

<style scoped lang="scss">
.dashboard {
    height: 100%;

    .dashboard-content {
        display: flex;
        justify-content: center;
        width: 100%;
        padding: 12px;
        position: absolute;
        bottom: 12px;
        left: 0;
    }

    .left {
        display: flex;
        gap: 12px;
        z-index: 99;

        .aerodrome {
            width: 280px;
            background: #fff;
            box-shadow: 0px 0px 4px 0px #0000001a;
            border-radius: 12px;

            &-header {
                display: flex;
                align-items: center;
                font-size: 14px;
                font-weight: bold;
                border-bottom: 1px solid #e5e5e5;
                padding: 12px;
                gap: 4px;

                .header-icon {
                    width: 20px;
                    height: 20px;
                }
            }

            &-content {
                display: flex;
                flex-direction: column;
                gap: 16px;
                padding: 16px 12px;

                .content-item {
                    display: flex;
                    align-items: center;
                    gap: 16px;

                    .content-item-icon {
                        width: 88px;
                        height: 88px;
                    }

                    .content-item-info {
                        display: flex;
                        flex-direction: column;
                        gap: 4px;

                        .info-name {
                            font-size: 14px;
                            color: #000000;
                        }

                        .info-value {
                            font-size: 20px;
                            font-weight: bold;
                            color: #000000;
                        }
                    }

                    .item-box {
                        display: flex;
                        flex-direction: column;
                        align-items: center;
                        gap: 4px;
                        background: #f6f6f6;
                        flex: 1;
                        border-radius: 10px;
                        padding: 8px;

                        .info-name {
                            font-size: 12px;
                            color: #000000;
                        }

                        .info-value {
                            font-size: 16px;
                            font-weight: bold;
                            color: #000000;
                        }
                    }
                }

                .content-item-ry {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    background: #f6f6f6;
                    padding: 8px 16px;
                    border-radius: 10px;

                    .item-name {
                        font-size: 14px;
                        color: #000000;
                    }

                    .item-value {
                        font-size: 16px;
                        font-weight: bold;
                        color: #000000;
                    }
                }
            }
        }
    }

    .right {
        display: flex;
        flex-direction: column;
        gap: 12px;
        overflow-y: auto;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0px 0px 4px 0px #0000001a;
        z-index: 99;

        .aerodrome {
            width: 320px;

            &-header {
                display: flex;
                align-items: center;
                font-size: 14px;
                font-weight: bold;
                border-bottom: 1px solid #e5e5e5;
                padding: 12px;
                gap: 4px;

                .header-icon {
                    width: 20px;
                    height: 20px;
                }
            }

            &-content {
                display: flex;
                flex-direction: column;
                gap: 8px;
                padding: 8px 10px;

                .content-item {
                    display: flex;
                    gap: 12px;
                    padding: 8px;
                    cursor: pointer;
                    border-radius: 4px;
                    border: 1px solid transparent;

                    &:hover {
                        background: #f6f6f6;
                    }

                    &.active {
                        background: #f6f6f6;
                        border: 1px solid #00386d;
                    }

                    .content-item-icon {
                        width: 88px;
                        height: 88px;
                    }

                    .content-item-right {
                        flex: 1;
                        display: flex;
                        flex-direction: column;
                        gap: 8px;

                        .right-title {
                            font-size: 14px;
                            font-weight: bold;
                            color: #000000;
                        }

                        .right-info {
                            display: flex;
                            gap: 4px;

                            .info-name {
                                font-size: 12px;
                                width: 5em;
                            }

                            .info-value {
                                flex: 1;
                                font-size: 12px;
                                color: #000000;
                                white-space: wrap;
                            }
                        }
                    }
                }
            }
        }
    }
}
</style>
