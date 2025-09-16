<template>
    <div class="status-section">
        <!-- 机场部分 -->
        <div class="section airport-section" v-if="deviceData.position_state">
            <div class="section-header">
                <h3 class="section-title">机场</h3>
                <span class="status-badge active">{{ getModeCode(deviceData.mode_code) }}</span>
                <span class="status-badge">当前正常</span>
            </div>

            <div class="airport-content">
                <!-- 左侧设备信息 -->
                <div class="airport-left">
                    <div class="device-image">
                        <img src="/img/dashboard/jc.png" alt="" class="placeholder-image" />
                    </div>
                    <div class="device-info-text">
                        <h4>{{ deviceData.name }}</h4>
                        <p>机场名称：机场4</p>
                        <p>主控SN：{{ gateway_sn }}</p>
                    </div>
                    <!-- <div class="service-info">
                        <div class="service-item">
                            <span class="service-label">保养服务</span>
                            <span class="service-value">127天/1467架次</span>
                        </div>
                        <div class="service-item">
                            <span class="service-label">行业无忧</span>
                            <span class="service-value">未绑定</span>
                        </div>
                    </div> -->
                </div>

                <!-- 右侧状态网格 -->
                <div class="airport-right">
                    <div class="status-grid-container">
                        <div class="status-row">
                            <div class="status-card">
                                <div class="status-label">累计运行时长</div>
                                <div class="status-value">{{ runDays }}天</div>
                            </div>
                            <div class="status-card">
                                <div class="status-label">作业架次</div>
                                <div class="status-value">
                                    {{ deviceData.maintain_status.maintain_status_array[0].last_maintain_work_sorties }}架次
                                </div>
                            </div>
                            <div class="status-card">
                                <div class="status-label">机场搜星</div>
                                <div class="status-value">{{ deviceData.position_state.rtk_number }}</div>
                            </div>
                            <div class="status-card">
                                <div class="status-label">标定状态</div>
                                <div class="status-value">{{ deviceData.position_state.is_calibration == 1 ? '已标定' : '未标定' }}</div>
                            </div>
                        </div>

                        <div class="status-row">
                            <div class="status-card">
                                <div class="status-label">网络</div>
                                <div class="status-value">{{ deviceData.network_state.rate }}kb/s</div>
                            </div>
                            <div class="status-card">
                                <div class="status-label">备降点</div>
                                <div class="status-value">已配置</div>
                            </div>
                            <div class="status-card">
                                <div class="status-label">空调状态</div>
                                <div class="status-value">{{ airConditionerState }}</div>
                            </div>
                            <div class="status-card">
                                <div class="status-label">蓄电池开关</div>
                                <div class="status-value">{{ deviceData.backup_battery.switch == 1 ? '已打开' : '已关闭' }}</div>
                            </div>
                        </div>

                        <div class="status-row">
                            <div class="status-card">
                                <div class="status-label">蓄电池电压</div>
                                <div class="status-value">{{ deviceData.backup_battery.voltage }}mV</div>
                            </div>
                            <div class="status-card">
                                <div class="status-label">蓄电池温度</div>
                                <div class="status-value">{{ deviceData.backup_battery.temperature }}℃</div>
                            </div>
                            <div class="status-card">
                                <div class="status-label">舱内温度</div>
                                <div class="status-value">{{ deviceData.temperature }}℃</div>
                            </div>
                            <div class="status-card">
                                <div class="status-label">舱内湿度</div>
                                <div class="status-value">{{ deviceData.humidity }}%</div>
                            </div>
                        </div>

                        <div class="status-row">
                            <div class="status-card">
                                <div class="status-label">舱外温度</div>
                                <div class="status-value">{{ deviceData.environment_temperature }}℃</div>
                            </div>
                            <div class="status-card">
                                <div class="status-label">雨量</div>
                                <div class="status-value">{{ rainfall }}</div>
                            </div>
                            <div class="status-card">
                                <div class="status-label">风速</div>
                                <div class="status-value">{{ deviceData.wind_speed }}m/s</div>
                            </div>
                            <div class="status-card empty-card">
                                <div class="status-label"></div>
                                <div class="status-value"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 飞行器部分 -->
        <div class="section aircraft-section">
            <div class="section-header">
                <h3 class="section-title">飞行器</h3>
                <span class="status-badge active">{{ deviceData.sub_device.device_online_status == 0 ? '舱内关机' : '开机' }}</span>
                <span class="status-badge">N/A</span>
            </div>

            <div class="aircraft-content">
                <!-- 左侧设备信息 -->
                <div class="aircraft-left">
                    <div class="device-image">
                        <img src="/img/dashboard/wrj.png" alt="" class="placeholder-image" />
                    </div>
                    <div class="device-info-text">
                        <h4>{{ deviceData.name }}</h4>
                        <p>飞行器名称：M3D</p>
                        <p>主控SN：{{ deviceData.sub_device.device_sn }}</p>
                    </div>
                    <!-- <div class="service-info">
                        <div class="service-item">
                            <span class="service-label">保养服务</span>
                            <span class="service-value">2938航时/309天/963架次</span>
                        </div>
                        <div class="service-item">
                            <span class="service-label">行业无忧</span>
                            <span class="service-value">未绑定</span>
                        </div>
                    </div> -->
                </div>

                <!-- 右侧状态网格 -->
                <div class="aircraft-right">
                    <div class="status-grid-container">
                        <div class="status-row">
                            <!-- <div class="status-card">
                                <div class="status-label">累计飞行时长</div>
                                <div class="status-value">{{ deviceData.flightTime }}</div>
                            </div> -->
                            <div class="status-card">
                                <div class="status-label">飞行架次</div>
                                <div class="status-value">
                                    {{ deviceData.maintain_status.maintain_status_array[0].last_maintain_work_sorties }}架次
                                </div>
                            </div>
                            <div class="status-card">
                                <div class="status-label">图传模式</div>
                                <div class="status-value">{{ deviceData.wireless_link.link_workmode == 1 ? '4G 融合模式' : 'SDR 模式' }}</div>
                            </div>
                            <div class="status-card">
                                <div class="status-label">搜星状态</div>
                                <div class="status-value">{{ deviceData.position_state.is_calibration == 1 ? '已标定' : '未标定' }}</div>
                            </div>
                        </div>

                        <div class="status-row">
                            <div class="battery-card">
                                <div class="battery-header">
                                    <div class="status-label">电池</div>
                                </div>
                                <!-- <div class="battery-item">
                                    <div class="battery-label">循环次数</div>
                                    <div class="battery-value">13次</div>
                                </div>
                                <div class="battery-item">
                                    <div class="battery-label">高电量存储</div>
                                    <div class="battery-value">0天</div>
                                </div> -->
                                <div class="battery-item">
                                    <div class="battery-label">电压</div>
                                    <div class="battery-value">{{ deviceData.drone_battery_maintenance_info.batteries[0].voltage }}/mV</div>
                                </div>
                                <div class="battery-item">
                                    <div class="battery-label">温度</div>
                                    <div class="battery-value">{{ deviceData.drone_battery_maintenance_info.batteries[0].temperature }}℃</div>
                                </div>
                                <div class="battery-item">
                                    <div class="battery-label">电量</div>
                                    <div class="battery-value">{{ deviceData.drone_battery_maintenance_info.batteries[0].capacity_percent }}%</div>
                                </div>
                            </div>
                        </div>

                        <div class="status-row">
                            <!-- <div class="status-card">
                                <div class="status-label">飞行器夜航灯</div>
                                <div class="status-value">关闭</div>
                            </div> -->
                            <!-- <div class="status-card">
                                <div class="status-label">备降转移高</div>
                                <div class="status-value">50m</div>
                            </div>
                            <div class="status-card">
                                <div class="status-label">限高</div>
                                <div class="status-value">1500m</div>
                            </div>
                            <div class="status-card">
                                <div class="status-label">限远</div>
                                <div class="status-value">关闭</div>
                            </div> -->
                        </div>

                        <div class="status-row">
                            <!-- <div class="status-card">
                                <div class="status-label">避障</div>
                                <div class="status-value">开启</div>
                            </div> -->
                            <div class="status-card">
                                <div class="status-label">运行模式</div>
                                <div class="status-value">{{ deviceData.battery_store_mode == 1 ? '计划模式' : '待命模式' }}</div>
                            </div>
                            <div class="status-card empty-card">
                                <div class="status-label"></div>
                                <div class="status-value"></div>
                            </div>
                            <div class="status-card empty-card">
                                <div class="status-label"></div>
                                <div class="status-value"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script lang="ts" setup>
import { ref, computed, inject } from 'vue'
import { useMqttStore } from '/@/stores/mqtt'

const mqttStore = useMqttStore()

const deviceData = computed(() => mqttStore.deviceData)
const gateway_sn = computed(() => mqttStore.gateway_sn)

// 降雨量
const rainfall = computed(() => {
    if (deviceData.value && typeof deviceData.value.rainfall !== 'undefined') {
        switch (deviceData.value.rainfall) {
            case 0:
                return '无降雨'
            case 1:
                return '小雨'
            case 2:
                return '中雨'
            case 3:
                return '大雨'
            default:
                return '未知'
        }
    }
    return '未知'
})

// 空调状态
// {"0":"空闲模式(无制冷、制热、除湿等)","1":"制冷模式","2":"制热模式","3":"除湿模式","4":"制冷退出模式","5":"制热退出模式","6":"除湿退出模式","7":"制冷准备模式","8":"制热准备模式","9":"除湿准备模式"10":"风冷准备中"11":"风冷中"12":"风冷退出中"13":"除雾准备中"14":"除雾中"15":"除雾退出中"}
const airConditionerState = computed(() => {
    if (deviceData.value && deviceData.value.air_conditioner && typeof deviceData.value.air_conditioner.air_conditioner_state !== 'undefined') {
        switch (deviceData.value.air_conditioner.air_conditioner_state) {
            case 0:
                return '空闲模式'
            case 1:
                return '制冷模式'
            case 2:
                return '制热模式'
            case 3:
                return '除湿模式'
            case 4:
                return '制冷退出模式'
            case 5:
                return '制热退出模式'
            case 6:
                return '除湿退出模式'
            case 7:
                return '制冷准备模式'
            case 8:
                return '制热准备模式'
            case 9:
                return '除湿准备模式'
            case 10:
                return '风冷准备中'
            case 11:
                return '风冷中'
            case 12:
                return '风冷退出中'
            case 13:
                return '除雾准备中'
            case 14:
                return '除雾中'
            case 15:
                return '除雾退出中'
            default:
                return '未知'
        }
    }
    return '未知'
})

// 运行天数
const runDays = computed(() => {
    if (deviceData.value && typeof deviceData.value.acc_time !== 'undefined') {
        return Math.floor(deviceData.value.acc_time / 3600 / 24)
    }
    return '未知'
})

// 获取模式码
const getModeCode = (modeCode: number) => {
    switch (modeCode) {
        case 0:
            return '设备空闲中'
        case 1:
            return '现场调试'
        case 2:
            return '远程调试'
        case 3:
            return '固件升级中'
        case 4:
            return '作业中'
        case 5:
            return '待标定'
        default:
            return '未知'
    }
}
</script>

<style scoped>
.status-section {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.section {
    border: 1px solid #eaeaea;
    padding: 24px;
    border-radius: 10px;
}

.section-header {
    display: flex;
    align-items: center;
    padding-bottom: 12px;
    gap: 12px;
}

.section-title {
    font-size: 18px;
    font-weight: 600;
    color: #333;
    margin: 0;
}

.status-badge {
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 400;
    background: transparent;
    color: rgba(0, 0, 0, 0.4);
    border: 1px solid rgba(0, 0, 0, 0.4);
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.status-badge.active {
    background: transparent;
    color: #2ba471;
    border-color: #2ba471;
}

.status-badge.active::before {
    content: '';
    width: 8px;
    height: 8px;
    background: #2ba471;
    border-radius: 50%;
}

/* 机场和飞行器内容样式 */
.airport-content,
.aircraft-content {
    display: flex;
    gap: 24px;
    align-items: flex-start;
}

.airport-left,
.aircraft-left {
    width: 185px;
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.airport-right,
.aircraft-right {
    flex: 1;
}

.device-image {
    display: flex;
    justify-content: center;
    margin-bottom: 12px;
}

.placeholder-image {
    width: 130px;
    height: 130px;
    border-radius: 0;
    flex-shrink: 0;
}

.device-info-text {
    display: flex;
    flex-direction: column;
    gap: 4px;
    padding-bottom: 10px;
    margin-bottom: 10px;
    width: 100%;
    text-align: center;
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
}

.device-info-text h4 {
    font-size: 14px;
    font-weight: 400;
    color: #000;
    margin: 0;
    margin-bottom: 8px;
}

.device-info-text p {
    font-size: 12px;
    color: #000;
    margin: 0;
    font-weight: 400;
}

.service-info {
    display: flex;
    flex-direction: column;
    gap: 8px;
    width: 100%;
}

.service-item {
    display: flex;
    gap: 8px;
    align-items: center;
    font-size: 12px;
}

.service-label {
    color: rgba(0, 0, 0, 0.6);
    font-weight: 400;
}

.service-value {
    color: #000;
    font-weight: 400;
}

/* 状态网格容器 */
.status-grid-container {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.status-row {
    display: flex;
    gap: 8px;
}

.status-card {
    flex: 1;
    background: #f1f5f9;
    border-radius: 8px;
    padding: 12px 8px;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 4px;
    justify-content: center;
}

.status-card.empty-card {
    background: transparent;
}

.status-label {
    font-size: 12px;
    color: rgba(0, 0, 0, 0.6);
    font-weight: 400;
    line-height: 1.2;
}

.status-value {
    font-size: 14px;
    color: #000;
    font-weight: 500;
    line-height: 1.2;
}

/* 电池卡片特殊样式 */
.battery-card {
    flex: 4;
    background: #f1f5f9;
    border-radius: 8px;
    display: flex;
    gap: 8px;
    padding: 12px;
}

.battery-header {
    display: flex;
    align-items: center;
    margin-bottom: 4px;
    flex: 1;
    justify-content: center;
}

.battery-header .status-label {
    font-size: 12px;
    color: rgba(0, 0, 0, 0.6);
    font-weight: bold;
}

.battery-grid {
    display: flex;
    gap: 16px;
    justify-content: space-between;
}

.battery-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 2px;
    flex: 1;
}

.battery-label {
    font-size: 10px;
    color: rgba(0, 0, 0, 0.6);
    font-weight: 400;
    line-height: 1.2;
}

.battery-value {
    font-size: 12px;
    color: #000;
    font-weight: 500;
    line-height: 1.2;
}
</style>
