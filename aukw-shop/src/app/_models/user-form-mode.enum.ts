/* 
    Example of extending an enum
    From https://github.com/microsoft/TypeScript/issues/17592#issuecomment-449440944
*/
import { FormMode } from './form-mode.enum';
enum AdditionalMode {
  Profile = 'Profile',
}
export type UserFormMode = FormMode | AdditionalMode;
export const UserFormMode = { ...FormMode, ...AdditionalMode };
