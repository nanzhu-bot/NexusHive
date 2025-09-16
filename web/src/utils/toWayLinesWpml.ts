import { shipForm } from '@/views/backend/flightSpace/type/shipLanes-type'

// 没有的参数
const noParam = [
    'autoRerouteInfo',
    'templateType',
    'globalWaypointTurnMode',
    'waylineCoordinateSysParam',
    'globalHeight',
    'globalWaypointHeadingParam',
    'gimbalPitchMode',
    'payloadParam',
    'globalUseStraightLine',
    'useGlobalHeight',
    'useGlobalSpeed',
    'useGlobalHeading',
    'useGlobalTurnParam',
]

/**
 * Converts input data into a KML template string.
 * @param {string} data - The name of the KML object.
 * @returns {string} - The formatted KML string.
 */
function toWayLinesWpml(data: shipForm) {
    let missionConfigText = ''
    // missionConfig: {
    //     flyToWaylineMode: 'safely', //航线飞行模式 safely：垂直爬升 pointToPoint：倾斜爬升
    //     finishAction: 'goHome', //完成动作 noAction: 退出航线模式  autoLand: 原地降落 goHome：自动返航 gotoFirstWaypoint: 返回起飞点悬停
    //     takeOffSecurityHeight: 20, //起飞安全高度
    //     executeRCLostAction:'goBack', //失联行为 goBack：自动返航 landing：原地降落 hover：悬停
    //     globalRTHHeight:100, //全局返航高度
    // },
    const missionConfig = data.missionConfig
    for (const key in missionConfig) {
        if (missionConfig[key] === undefined) {
            continue
        }
        if (key == 'autoRerouteInfo') {
            continue
        }
        if (key != 'takeOffRefPoint') {
            missionConfigText += toXmlText(key, missionConfig[key], 2)
        }
    }
    let FolderText = ''
    const Folder = data.Folder
    for (const key in Folder) {
        if (Folder[key] === undefined) {
            continue
        }
        if (noParam.includes(key)) {
            continue
        }
        FolderText += toXmlText(key, Folder[key], 2)
    }
    return `<?xml version="1.0" encoding="UTF-8"?>
<kml xmlns="http://www.opengis.net/kml/2.2" xmlns:wpml="http://www.dji.com/wpmz/1.0.6">
  <Document>
    <wpml:missionConfig>
      ${missionConfigText}
    </wpml:missionConfig>
    <Folder>
      ${FolderText}
    </Folder>
  </Document>
</kml>`
}

function toXmlText(key: string, data: any, indentLevel: number = 0) {
    const indent = '  '.repeat(indentLevel)
    let text = ''

    // 特殊处理payloadLensIndex，直接合并为逗号分隔的字符串
    // if (key == 'payloadLensIndex' && Array.isArray(data)) {
    //     const payloadLensIndexString = data.join(',')
    //     return `${indent}<wpml:${key}>${payloadLensIndexString}</wpml:${key}>\n`
    // }

    if (Array.isArray(data)) {
        // 特殊处理imageFormat，直接合并为逗号分隔的字符串
        if (key == 'imageFormat' || key == 'payloadLensIndex') {
            const imageFormatString = data.join(',')
            return `${indent}<wpml:${key}>${imageFormatString}</wpml:${key}>\n`
        }

        // 对于数组，为每个元素创建独立的标签
        for (let i = 0; i < data.length; i++) {
            if (key == 'action') {
                // 为每个action创建独立的标签
                text += `${indent}<wpml:${key}>\n`
                text += toXmlText('actionId', i, indentLevel + 1)
                text += toXmlText('actionActuatorFunc', data[i].actionActuatorFunc, indentLevel + 1)
                text += `${indent}  <wpml:actionActuatorFuncParam>\n`
                for (const k in data[i].actionActuatorFuncParam) {
                    if (data[i].actionActuatorFuncParam[k] === undefined) {
                        continue
                    }
                    text += toXmlText(k, data[i].actionActuatorFuncParam[k], indentLevel + 2)
                }
                text += `${indent}  </wpml:actionActuatorFuncParam>\n`
                text += `${indent}</wpml:${key}>\n`
            } else if (key == 'Placemark') {
                // 为每个Placemark创建独立的标签
                text += `${indent}<${key}>\n`
                text += toArrayText(key, data[i], indentLevel + 1)
                text += `${indent}</${key}>\n`
            } else {
                // 对于其他数组，先创建开始标签，然后处理所有元素，最后创建结束标签
                if (i === 0) {
                    text += `${indent}<wpml:${key}>\n`
                }
                text += toArrayText(key, data[i], indentLevel + 1)
                if (i === data.length - 1) {
                    text += `${indent}</wpml:${key}>\n`
                }
            }
        }
    } else if (typeof data === 'object') {
        if (noParam.includes(key)) {
            return ''
        }
        // 对于对象，先创建开始标签，然后递归处理所有属性，最后创建结束标签
        if (key === 'Point') {
            text += `${indent}<${key}>\n`
        } else {
            text += `${indent}<wpml:${key}>\n`
        }

        // 递归处理对象的所有属性
        for (const k in data) {
            text += toXmlText(k, data[k], indentLevel + 1)
        }

        if (key === 'Point') {
            text += `${indent}</${key}>\n`
        } else {
            text += `${indent}</wpml:${key}>\n`
        }
    } else {
        if (key === 'coordinates') {
            text += `${indent}<${key}>\n${indent}  ${data}\n${indent}</${key}>\n`
        } else {
            text += `${indent}<wpml:${key}>${data}</wpml:${key}>\n`
        }
    }
    return text
}

function toArrayText(k: string, data: any, indentLevel: number = 0) {
    let text = ''
    for (const key in data) {
        text += toXmlText(key, data[key], indentLevel)
    }
    return text
}

export default toWayLinesWpml
