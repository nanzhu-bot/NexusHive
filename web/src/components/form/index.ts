// 表单组件统一导出
export { default as CustomInput } from './CustomInput.vue'
export { default as CustomSelect } from './CustomSelect.vue'
export { default as CustomButton } from './CustomButton.vue'
export { default as CustomRadioGroup } from '../CustomRadioGroup.vue'

// 类型定义
export interface SelectOption {
  value: string | number | boolean
  label: string
  disabled?: boolean
  icon?: string
}

export interface RadioOption {
  value: string | number | boolean
  label: string
  icon?: string
}