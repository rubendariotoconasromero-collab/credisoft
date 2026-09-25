import catalog from './data/departamentos-bolivia.json';

export const departamentosBolivia = Object.freeze(catalog.map(department => Object.freeze({ ...department })));

const comparisonKey = value => String(value ?? '')
    .trim()
    .replace(/\s+/g, ' ')
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLocaleLowerCase('es');

export function normalizeDepartamento(value) {
    if (!String(value ?? '').trim()) return '';
    const match = departamentosBolivia.find(department => comparisonKey(department.nombre) === comparisonKey(value));
    return match?.nombre ?? String(value).trim();
}
