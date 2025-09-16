/**
 * 航线设置接口
 * 用于定义无人机航线飞行的完整配置
 */
export interface shipForm {
    /** 任务配置参数 */
    missionConfig: MissionConfig
    /** 航线文件夹配置 */
    Folder: Folder
}

/**
 * 动作参数接口
 * 定义各种飞行动作的具体参数
 */
export interface actionActuatorFuncParam {
    /** 单拍动作参数 */
    takePhoto: takePhoto
    /** 悬停等待参数 */
    hover: hover
    /** 开始录像参数 */
    startRecord: startRecord
    /** 结束录像参数 */
    stopRecord: stopRecord
    /** 对焦参数 */
    focus: focus
    /** 变焦参数 */
    zoom: zoom
    /** 旋转云台参数 */
    gimbalRotate: gimbalRotate
    /** 飞行器偏航参数 */
    rotateYaw: rotateYaw
}

/**
 * 航线文件夹配置接口
 * 定义航线的全局参数和航点信息
 */
export interface Folder {
    /**
     * 预定义模版类型
     * - waypoint: 航点飞行
     * - mapping2d: 建图航拍
     * - mapping3d: 倾斜摄影
     * - mappingStrip: 航带飞行
     */
    templateType?: string
    /** 模版ID，范围 [0, 65535] */
    templateId: number
    /** 坐标系参数 */
    waylineCoordinateSysParam: {
        /**
         * 航点高程参考平面
         * - EGM96: 使用海拔高编辑
         * - relativeToStartPoint: 相对点
         * - aboveGroundLevel: 地形数据
         */
        heightMode: string
        /** 经纬度坐标系，当前固定使用WGS84坐标系 */
        coordinateMode: string
    }
    /** 全局航线高度（米） */
    globalHeight: number
    /** 全局航线速度，范围 [1, 15] m/s */
    autoFlightSpeed: number
    /**
     * 航点类型
     * - coordinateTurn: 协调转弯，不过点，提前转弯
     * - toPointAndPassWithContinuityCurvature: 平滑过点，提前转弯
     */
    globalWaypointTurnMode: string
    /** 飞行器偏航角模式配置 */
    globalWaypointHeadingParam: {
        /**
         * 航点偏航角模式
         * - followWayline: 沿航线方向
         * - manually: 手动控制
         * - fixed: 锁定当前偏航角
         */
        waypointHeadingMode: string
        /**
         * 飞行器偏航角转动方向
         * - clockwise: 顺时针旋转飞行器偏航角
         * - counterClockwise: 逆时针旋转飞行器偏航角
         * - followBadArc: 沿最短路径旋转飞行器偏航角
         */
        waypointHeadingPathMode: string
    }
    /**
     * 航点间云台俯仰角控制模式
     * - manual: 手动控制
     * - usePointSetting: 依照每个航点设置
     */
    gimbalPitchMode: string
    /** 负载设置参数 */
    payloadParam: {
        /** 负载挂载位置 */
        payloadPositionIndex: string
        /**
         * 图片格式列表
         * - wide: 存储广角镜头照片
         * - zoom: 存储变焦镜头照片
         * - ir: 存储红外镜头照片
         * - narrow_band: 存储窄带镜头拍摄照片
         * - visable: 可见光照片
         */
        imageFormat: string[]
    }
    /**
     * 全局航段轨迹是否尽量贴合直线
     * - 0: 航段轨迹全程为曲线
     * - 1: 航段轨迹尽量贴合两点连线
     */
    globalUseStraightLine: boolean | number
    /** 航点信息数组 */
    Placemark: Placemark[]
    /** 航线ID */
    // waylineId: number
    /** 航线高度模式 */
    // executeHeightMode: string
}

/**
 * 航点信息接口
 * 定义单个航点的详细参数
 */
export interface Placemark {
    /** 航点经纬度坐标，格式：<经度,纬度> */
    Point: {
        coordinates: string
    }
    /** 航点序号，范围 [0, 65535] */
    index: number
    /** 航线高度 */
    executeHeight: number
    /** 航线速度 */
    waypointSpeed: number
    /** 是否使用全局航线高度，0: 不使用，1: 使用 */
    useGlobalHeight: number
    /** 是否使用全局飞行速度，0: 不使用，1: 使用 */
    useGlobalSpeed: number
    /** 是否使用全局航线偏航角，0: 不使用，1: 使用 */
    useGlobalHeading: number
    /** 是否使用全局航点类型，0: 不使用，1: 使用 */
    useGlobalTurnParam: number
    /**
     * 该航段是否贴合直线
     * - 0: 航段轨迹全程为曲线
     * - 1: 航段轨迹尽量贴合两点连线
     */
    useStraightLine: number
    /** 云台俯仰角，范围 [0, 90] 度 */
    gimbalPitchAngle: number
    /** 航点动作 */
    actionGroup: actionGroup
    /** 是否危险点 */
    isRisky: number
}

/**
 * 任务配置接口
 * 定义飞行任务的安全参数和飞行器信息
 */
export interface MissionConfig {
    /**
     * 飞向航线模式
     * - safely: 垂直爬升
     * - pointToPoint: 倾斜爬升
     */
    flyToWaylineMode: string
    /**
     * 航线结束动作
     * - goHome: 自动返航
     * - noAction: 退出航线模式
     * - autoLand: 原地降落
     * - gotoFirstWaypoint: 返回航线起始点悬停
     */
    finishAction: string
    /**
     * 飞行器安全高度（米）
     * - 遥控器场景: [1.2, 1500]
     * - 机场场景: [8, 1500]
     */
    takeOffSecurityHeight: number
    /**
     * 失控是否继续执行航线
     * - goContinue: 继续执行航线
     * - executeLostAction: 退出航线，执行失控动作
     */
    exitOnRCLost: string
    /**
     * 失控动作
     * - goBack: 返航，飞行器从失控位置飞向起飞点
     * - landing: 降落，飞行器从失控位置原地降落
     * - hover: 悬停，飞行器从失控位置悬停
     */
    executeRCLostAction?: string
    /** 全局航线过渡速度，范围 [1, 15] m/s */
    globalTransitionalSpeed: number
    /** 全局返航高度，范围 [2, 1500] 米 */
    globalRTHHeight: number
    /** 起飞点参考点 */
    takeOffRefPoint: string
    /** 飞行器机型信息 */
    droneInfo: droneInfo
    /** 负载机型信息 */
    payloadInfo: payloadInfo
    /** 航线绕行配置 */
    autoRerouteInfo: autoRerouteInfo
}

/**
 * 飞行器机型信息接口
 */
interface droneInfo {
    /** 飞行器机型主类型 */
    droneEnumValue: string
    /** 飞行器机型子类型 */
    droneSubEnumValue: string
}

/**
 * 负载机型信息接口
 */
interface payloadInfo {
    /** 负载机型主类型 */
    payloadEnumValue: string
    /** 负载挂载位置 */
    payloadPositionIndex: string
}

/**
 * 航线绕行配置接口
 */
interface autoRerouteInfo {
    /** 任务航线绕行模式 */
    missionAutoRerouteMode: number
    /** 过渡航线绕行模式 */
    transitionalAutoRerouteMode: number
}

/**
 * 动作接口
 * 定义单个动作的基本信息
 */
export interface action {
    /** 动作ID */
    actionId: number
    /**
     * 动作类型
     * - takePhoto: 拍照
     * - startRecord: 开始录像
     * - stopRecord: 结束录像
     * - focus: 对焦
     * - zoom: 变焦
     * - gimbalRotate: 旋转云台
     * - rotateYaw: 飞行器偏航
     * - hover: 悬停等待
     */
    actionActuatorFunc: string
    /** 动作参数 */
    actionActuatorFuncParam?: takePhoto | startRecord | stopRecord | focus | zoom | gimbalRotate | rotateYaw | hover
}

/**
 * 航线初始动作组接口
 * 定义动作组的执行规则和触发条件
 */
export interface actionGroup {
    /** 动作组ID */
    actionGroupId: number
    /** 动作组开始生效的航点 */
    actionGroupStartIndex: number
    /** 动作组结束生效的航点 */
    actionGroupEndIndex: number
    /**
     * 动作执行模式
     * - sequence: 顺序执行
     *  */
    actionGroupMode: string
    /** 动作组触发器 */
    actionTrigger: {
        /**
         * 触发器类型
         * - reachPoint: 到达航点时执行
         * - betweenAdjacentPoints: 航段触发，均匀转云台
         * - multipleTiming: 等时触发
         * - multipleDistance: 等距触发
         */
        actionTriggerType: string
    }
    /** 动作列表 */
    action: action[]
}

/**
 * 单拍动作参数接口
 */
interface takePhoto {
    /** 负载挂载位置，相机枚举值中type-subtype-gimbalindex中的gimbalindex字段 */
    payloadPositionIndex: string
    /** 拍摄照片文件后缀 */
    fileSuffix: string
    /**
     * 拍摄照片存储类型
     * - wide: 存储广角镜头照片
     * - zoom: 存储变焦镜头照片
     * - ir: 存储红外镜头照片
     * - narrow_band: 存储窄带镜头拍摄照片
     * - visable: 可见光照片
     */
    payloadLensIndex: string[]
    /** 是否使用全局拍摄照片存储类型，0: 不使用，1: 使用 */
    useGlobalPayloadLensIndex: number
}

/**
 * 开始录像参数接口
 */
interface startRecord {
    /** 负载挂载位置，相机枚举值中type-subtype-gimbalindex中的gimbalindex字段 */
    payloadPositionIndex: string
    /** 拍摄照片文件后缀 */
    fileSuffix: string
    /**
     * 拍摄照片存储类型
     * - wide: 存储广角镜头照片
     * - zoom: 存储变焦镜头照片
     * - ir: 存储红外镜头照片
     * - narrow_band: 存储窄带镜头拍摄照片
     * - visable: 可见光照片
     */
    payloadLensIndex: string[]
    /** 是否使用全局拍摄照片存储类型，0: 不使用，1: 使用 */
    useGlobalPayloadLensIndex: number
}

/**
 * 结束录像参数接口
 */
interface stopRecord {
    /** 负载挂载位置，相机枚举值中type-subtype-gimbalindex中的gimbalindex字段 */
    payloadPositionIndex: string
    /**
     * 拍摄照片存储类型
     * - wide: 存储广角镜头照片
     * - zoom: 存储变焦镜头照片
     * - ir: 存储红外镜头照片
     * - narrow_band: 存储窄带镜头拍摄照片
     * - visable: 可见光照片
     */
    payloadLensIndex: string[]
}

/**
 * 对焦参数接口
 */
interface focus {
    /** 负载挂载位置，相机枚举值中type-subtype-gimbalindex中的gimbalindex字段 */
    payloadPositionIndex: string
    /** 是否点对焦，0: 区域对焦，1: 点对焦 */
    isPointFocus: number
    /** 对焦距离，范围 [0, 100] */
    focusDistance: number
    /**
     * 对焦点位置X坐标
     * 对焦点或对焦区域左上角在画面的X轴（宽）坐标
     * 0为最左侧，1为最右侧，范围 [0, 1]
     */
    focusX?: number
    /**
     * 对焦点位置Y坐标
     * 对焦点或对焦区域左上角在画面的Y轴（高）坐标
     * 0为最顶部，1为最底部，范围 [0, 1]
     */
    focusY?: number
    /**
     * 对焦区域宽度比
     * 对焦区域大小占画面整体的比例，此为宽度比
     * 当"isPointFocus"为"0"（即区域对焦）时必需
     */
    focusRegionWidth?: number
    /**
     * 对焦区域高度比
     * 对焦区域大小占画面整体的比例，此为高度比
     * 当"isPointFocus"为"0"（即区域对焦）时必需
     */
    focusRegionHeight?: number
    /** 是否无穷远对焦，0: 非无穷远对焦，1: 无穷远对焦 */
    isInfiniteFocus: number
}

/**
 * 变焦参数接口
 */
interface zoom {
    /** 负载挂载位置，相机枚举值中type-subtype-gimbalindex中的gimbalindex字段 */
    payloadPositionIndex: string
    /** 变焦倍数，范围 [0, 100] */
    focalLength: number
}

/**
 * 旋转云台参数接口
 */
interface gimbalRotate {
    /** 负载挂载位置，相机枚举值中type-subtype-gimbalindex中的gimbalindex字段 */
    payloadPositionIndex: string
    /** 云台偏航角转动坐标系，north: 相对地理北 */
    gimbalHeadingYawBase: string
    /** 云台转动模式，absoluteAngle: 绝对角度，相对于正北方的角度 */
    gimbalRotateMode: string
    /** 是否使能云台Pitch转动，0: 不使能，1: 使能 */
    gimbalPitchRotateEnable: number
    /** 云台Pitch转动角度，范围 [0, 90] 度 */
    gimbalPitchRotateAngle: number
    /** 是否使能云台Roll转动，0: 不使能，1: 使能 */
    gimbalRollRotateEnable: number
    /** 云台Roll转动角度，范围 [0, 90] 度 */
    gimbalRollRotateAngle: number
    /** 是否使能云台Yaw转动，0: 不使能，1: 使能 */
    gimbalYawRotateEnable: number
    /** 云台Yaw转动角度，范围 [0, 360] 度 */
    gimbalYawRotateAngle: number
    /** 是否使能云台转动时间，0: 不使能，1: 使能 */
    gimbalRotateTimeEnable: number
    /** 云台转动时间，范围 [0, 100] 秒 */
    gimbalRotateTime: number
}

/**
 * 飞行器偏航参数接口
 */
interface rotateYaw {
    /** 飞行器偏航角，范围 [-180, 180] 度 */
    aircraftHeading: number
    /**
     * 飞行器偏航角转动模式
     * - clockwise: 顺时针旋转飞行器偏航角
     * - counterClockwise: 逆时针旋转飞行器偏航角
     */
    aircraftPathMode: string
}

/**
 * 悬停等待参数接口
 */
interface hover {
    /** 飞行器悬停等待时间，必须大于0（秒） */
    hoverTime: number
}
