export const ROOT = "http://localhost/Openminds/public/";

const rootStyles = getComputedStyle(document.documentElement);

export const colors = {
    primary: rootStyles.getPropertyValue('--color-primary').trim(),
    error: rootStyles.getPropertyValue('--color-error').trim(),
    surface: rootStyles.getPropertyValue('--color-surface').trim(),
    secondaryBackground: rootStyles.getPropertyValue('--color-secondary-background').trim(),
    background: rootStyles.getPropertyValue('--color-background').trim(),
    border: rootStyles.getPropertyValue('--color-border').trim(),
    placeholder: rootStyles.getPropertyValue('--color-placeholder').trim(),
    text: rootStyles.getPropertyValue('--color-text').trim(),
    success: rootStyles.getPropertyValue('--color-success').trim(),
    greenText: rootStyles.getPropertyValue('--color-green-text').trim(),
    green: rootStyles.getPropertyValue('--color-green').trim(),
    yellow: rootStyles.getPropertyValue('--color-yellow').trim(),
    red: rootStyles.getPropertyValue('--color-red').trim()
  };