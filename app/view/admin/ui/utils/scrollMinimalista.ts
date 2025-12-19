/*
 * Scroll Minimalista
 * Maneja la visibilidad del scrollbar de forma dinamica
 * Solo aparece cuando el usuario esta scrolleando
 */

const CLASE_SCROLLEANDO = 'estaScrolleando';
const TIEMPO_OCULTAR = 1200;

let timeoutsScroll = new WeakMap<Element, ReturnType<typeof setTimeout>>();

/**
 * Inicializa el comportamiento de scroll minimalista
 * para todos los elementos con overflow
 */
export function inicializarScrollMinimalista(): void {
    document.addEventListener('scroll', manejarScroll, true);
    document.addEventListener('mouseenter', manejarMouseEnter, true);
    document.addEventListener('mouseleave', manejarMouseLeave, true);
}

/**
 * Maneja el evento de scroll
 */
function manejarScroll(evento: Event): void {
    const elemento = evento.target as Element;

    if (!esContenedorScrolleable(elemento)) return;

    elemento.classList.add(CLASE_SCROLLEANDO);

    const timeoutAnterior = timeoutsScroll.get(elemento);
    if (timeoutAnterior) {
        clearTimeout(timeoutAnterior);
    }

    const nuevoTimeout = setTimeout(() => {
        elemento.classList.remove(CLASE_SCROLLEANDO);
        timeoutsScroll.delete(elemento);
    }, TIEMPO_OCULTAR);

    timeoutsScroll.set(elemento, nuevoTimeout);
}

/**
 * Maneja cuando el mouse entra a un contenedor
 */
function manejarMouseEnter(evento: Event): void {
    const elemento = evento.target as Element;
    if (!esContenedorScrolleable(elemento)) return;

    elemento.classList.add(CLASE_SCROLLEANDO);
}

/**
 * Maneja cuando el mouse sale de un contenedor
 */
function manejarMouseLeave(evento: Event): void {
    const elemento = evento.target as Element;
    if (!esContenedorScrolleable(elemento)) return;

    const timeout = setTimeout(() => {
        elemento.classList.remove(CLASE_SCROLLEANDO);
    }, 500);

    timeoutsScroll.set(elemento, timeout);
}

/**
 * Verifica si un elemento es un contenedor scrolleable
 */
function esContenedorScrolleable(elemento: Element): boolean {
    if (!(elemento instanceof HTMLElement)) return false;

    const esScrolleable = elemento.scrollHeight > elemento.clientHeight || elemento.scrollWidth > elemento.clientWidth;

    const tieneOverflow = elemento.classList.contains('scrollMinimalista') || elemento.classList.contains('contenidoPrincipal') || elemento.classList.contains('barraLateral') || elemento.tagName === 'HTML' || elemento.tagName === 'BODY';

    return esScrolleable || tieneOverflow;
}

/**
 * Limpia todos los timeouts y eventos
 */
export function destruirScrollMinimalista(): void {
    document.removeEventListener('scroll', manejarScroll, true);
    document.removeEventListener('mouseenter', manejarMouseEnter, true);
    document.removeEventListener('mouseleave', manejarMouseLeave, true);
}
