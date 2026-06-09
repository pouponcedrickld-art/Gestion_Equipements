/**
 * Animations GSAP pour le calendrier de maintenance
 */
import { gsap } from 'gsap'

/**
 * Anime l'entrée de la grille du calendrier au montage
 * @param {HTMLElement} gridElement - Élément DOM de la grille
 * @returns {gsap.core.Timeline} Timeline GSAP pour nettoyage ultérieur
 */
export function animateCalendarEntry(gridElement) {
  if (!gridElement) return null

  const timeline = gsap.timeline()

  // Animation de la grille principale
  timeline.from(gridElement, {
    opacity: 0,
    y: 20,
    duration: 0.5,
    ease: 'power2.out'
  })

  // Animation des cellules de jour en cascade
  const dayCells = gridElement.querySelectorAll('[data-day-cell]')
  if (dayCells.length > 0) {
    timeline.from(
      dayCells,
      {
        opacity: 0,
        scale: 0.95,
        duration: 0.3,
        stagger: {
          amount: 0.4,
          from: 'start',
          ease: 'power1.out'
        }
      },
      '-=0.3' // Overlap avec l'animation précédente
    )
  }

  return timeline
}

/**
 * Anime l'ouverture d'une modale de détails
 * @param {HTMLElement} modalElement - Élément DOM de la modale
 * @returns {gsap.core.Timeline} Timeline GSAP
 */
export function animateModalOpen(modalElement) {
  if (!modalElement) return null

  const backdrop = modalElement.querySelector('[data-modal-backdrop]')
  const panel = modalElement.querySelector('[data-modal-panel]')

  const timeline = gsap.timeline()

  // Animation du backdrop
  if (backdrop) {
    timeline.from(backdrop, {
      opacity: 0,
      duration: 0.3,
      ease: 'power2.out'
    })
  }

  // Animation du panneau de contenu
  if (panel) {
    timeline.from(
      panel,
      {
        opacity: 0,
        y: 30,
        scale: 0.95,
        duration: 0.4,
        ease: 'back.out(1.2)'
      },
      '-=0.2'
    )
  }

  return timeline
}

/**
 * Anime la fermeture d'une modale
 * @param {HTMLElement} modalElement - Élément DOM de la modale
 * @param {Function} onComplete - Callback appelé après l'animation
 * @returns {gsap.core.Timeline} Timeline GSAP
 */
export function animateModalClose(modalElement, onComplete) {
  if (!modalElement) {
    if (onComplete) onComplete()
    return null
  }

  const backdrop = modalElement.querySelector('[data-modal-backdrop]')
  const panel = modalElement.querySelector('[data-modal-panel]')

  const timeline = gsap.timeline({
    onComplete: onComplete || null
  })

  // Animation du panneau
  if (panel) {
    timeline.to(panel, {
      opacity: 0,
      y: -20,
      scale: 0.95,
      duration: 0.3,
      ease: 'power2.in'
    })
  }

  // Animation du backdrop
  if (backdrop) {
    timeline.to(
      backdrop,
      {
        opacity: 0,
        duration: 0.2,
        ease: 'power2.in'
      },
      '-=0.1'
    )
  }

  return timeline
}

/**
 * Anime l'apparition des cartes d'événements dans une journée
 * @param {Array<HTMLElement>} cardsArray - Tableau d'éléments DOM des cartes
 * @returns {gsap.core.Timeline} Timeline GSAP
 */
export function animateEventCards(cardsArray) {
  if (!cardsArray || cardsArray.length === 0) return null

  return gsap.from(cardsArray, {
    opacity: 0,
    y: 10,
    duration: 0.3,
    stagger: 0.05,
    ease: 'power2.out'
  })
}

/**
 * Anime un changement de mois (transition)
 * @param {HTMLElement} gridElement - Élément DOM de la grille
 * @param {string} direction - 'left' ou 'right'
 * @returns {Promise} Résolu quand l'animation est terminée
 */
export function animateMonthTransition(gridElement, direction = 'left') {
  if (!gridElement) return Promise.resolve()

  const timeline = gsap.timeline()

  // Sortie
  timeline.to(gridElement, {
    opacity: 0,
    x: direction === 'left' ? -30 : 30,
    duration: 0.25,
    ease: 'power2.in'
  })

  // Entrée
  timeline.fromTo(
    gridElement,
    {
      opacity: 0,
      x: direction === 'left' ? 30 : -30
    },
    {
      opacity: 1,
      x: 0,
      duration: 0.3,
      ease: 'power2.out'
    }
  )

  return new Promise((resolve) => {
    timeline.eventCallback('onComplete', resolve)
  })
}

/**
 * Anime le hover sur une carte d'événement
 * @param {HTMLElement} cardElement - Élément DOM de la carte
 * @returns {gsap.core.Tween}
 */
export function animateCardHover(cardElement) {
  if (!cardElement) return null

  return gsap.to(cardElement, {
    scale: 1.03,
    boxShadow: '0 8px 20px rgba(0, 0, 0, 0.15)',
    duration: 0.2,
    ease: 'power1.out'
  })
}

/**
 * Réinitialise l'animation de hover
 * @param {HTMLElement} cardElement - Élément DOM de la carte
 * @returns {gsap.core.Tween}
 */
export function resetCardHover(cardElement) {
  if (!cardElement) return null

  return gsap.to(cardElement, {
    scale: 1,
    boxShadow: '0 2px 8px rgba(0, 0, 0, 0.1)',
    duration: 0.2,
    ease: 'power1.in'
  })
}

/**
 * Nettoie toutes les animations GSAP actives
 * Doit être appelé dans onUnmounted des composants
 */
export function cleanupAnimations() {
  gsap.killTweensOf('*')
}

/**
 * Anime le slide-in d'un panneau latéral (pour NotificationCenter)
 * @param {HTMLElement} panelElement - Élément DOM du panneau
 * @returns {gsap.core.Timeline} Timeline GSAP
 */
export function animateSlideInPanel(panelElement) {
  if (!panelElement) return null

  const timeline = gsap.timeline()

  timeline.from(panelElement, {
    x: 300,
    opacity: 0,
    duration: 0.4,
    ease: 'power3.out'
  })

  return timeline
}

/**
 * Anime le slide-out d'un panneau latéral
 * @param {HTMLElement} panelElement - Élément DOM du panneau
 * @param {Function} onComplete - Callback après l'animation
 * @returns {gsap.core.Timeline} Timeline GSAP
 */
export function animateSlideOutPanel(panelElement, onComplete) {
  if (!panelElement) {
    if (onComplete) onComplete()
    return null
  }

  const timeline = gsap.timeline({
    onComplete: onComplete || null
  })

  timeline.to(panelElement, {
    x: 300,
    opacity: 0,
    duration: 0.3,
    ease: 'power2.in'
  })

  return timeline
}
