/**
 * Utilitaires de formatage et manipulation de dates pour le calendrier de maintenance
 */

/**
 * Formate une date en français (format court)
 * @param {Date|string} date - Date à formater
 * @returns {string} Format: "jj/mm/aaaa"
 */
export function formatDate(date) {
  try {
    const d = typeof date === 'string' ? new Date(date) : date
    if (isNaN(d.getTime())) return 'Date invalide'
    
    const day = String(d.getDate()).padStart(2, '0')
    const month = String(d.getMonth() + 1).padStart(2, '0')
    const year = d.getFullYear()
    
    return `${day}/${month}/${year}`
  } catch (error) {
    console.error('Error formatting date:', error)
    return 'Date invalide'
  }
}

/**
 * Formate une date en français (format court) (alias)
 * @param {Date|string} date - Date à formater
 * @returns {string} Format: "jj/mm/aaaa"
 */
export function formatDateFr(date) {
  return formatDate(date);
}

/**
 * Formate une date en format long en français
 * @param {Date|string} date - Date à formater
 * @returns {string} Format: "lundi 8 juin 2026"
 */
export function formatDateLong(date) {
  try {
    const d = typeof date === 'string' ? new Date(date) : date
    if (isNaN(d.getTime())) return 'Date invalide'
    
    const options = { 
      weekday: 'long', 
      year: 'numeric', 
      month: 'long', 
      day: 'numeric' 
    }
    
    return d.toLocaleDateString('fr-FR', options)
  } catch (error) {
    console.error('Error formatting date long:', error)
    return 'Date invalide'
  }
}

/**
 * Formate l'heure d'une date
 * @param {Date|string} date - Date à formater
 * @returns {string} Format: "14:30"
 */
export function formatTime(date) {
  try {
    const d = typeof date === 'string' ? new Date(date) : date
    if (isNaN(d.getTime())) return 'Heure invalide'
    
    const hours = String(d.getHours()).padStart(2, '0')
    const minutes = String(d.getMinutes()).padStart(2, '0')
    
    return `${hours}:${minutes}`
  } catch (error) {
    console.error('Error formatting time:', error)
    return 'Heure invalide'
  }
}

/**
 * Parse une chaîne ISO 8601 en objet Date
 * @param {string} isoString - Chaîne au format ISO 8601
 * @returns {Date|null} Objet Date ou null si invalide
 */
export function parseISODate(isoString) {
  try {
    if (!isoString) return null
    const date = new Date(isoString)
    return isNaN(date.getTime()) ? null : date
  } catch (error) {
    console.error('Error parsing ISO date:', error)
    return null
  }
}

/**
 * Calcule la durée entre deux dates en heures
 * @param {Date|string} start - Date de début
 * @param {Date|string} end - Date de fin
 * @returns {number} Durée en heures (arrondie à 1 décimale)
 */
export function calculateDuration(start, end) {
  try {
    const startDate = typeof start === 'string' ? new Date(start) : start
    const endDate = typeof end === 'string' ? new Date(end) : end
    
    if (isNaN(startDate.getTime()) || isNaN(endDate.getTime())) {
      return 0
    }
    
    const diffMs = endDate - startDate
    const diffHours = diffMs / (1000 * 60 * 60)
    
    return Math.round(diffHours * 10) / 10 // Arrondi à 1 décimale
  } catch (error) {
    console.error('Error calculating duration:', error)
    return 0
  }
}

/**
 * Obtient le premier et dernier jour d'un mois donné
 * @param {Date|string} date - Date quelconque dans le mois
 * @returns {Object} { start: Date, end: Date }
 */
export function getMonthBounds(date) {
  try {
    const d = typeof date === 'string' ? new Date(date) : date
    if (isNaN(d.getTime())) {
      const now = new Date()
      return {
        start: new Date(now.getFullYear(), now.getMonth(), 1),
        end: new Date(now.getFullYear(), now.getMonth() + 1, 0, 23, 59, 59)
      }
    }
    
    const year = d.getFullYear()
    const month = d.getMonth()
    
    return {
      start: new Date(year, month, 1, 0, 0, 0),
      end: new Date(year, month + 1, 0, 23, 59, 59)
    }
  } catch (error) {
    console.error('Error getting month bounds:', error)
    const now = new Date()
    return {
      start: new Date(now.getFullYear(), now.getMonth(), 1),
      end: new Date(now.getFullYear(), now.getMonth() + 1, 0, 23, 59, 59)
    }
  }
}

/**
 * Formate une date avec heure complète
 * @param {Date|string} date - Date à formater
 * @returns {string} Format: "08/06/2026 à 14:30"
 */
export function formatDateTime(date) {
  try {
    const d = typeof date === 'string' ? new Date(date) : date
    if (isNaN(d.getTime())) return 'Date invalide'
    
    return `${formatDate(d)} à ${formatTime(d)}`
  } catch (error) {
    console.error('Error formatting datetime:', error)
    return 'Date invalide'
  }
}

/**
 * Vérifie si une date est aujourd'hui
 * @param {Date|string} date - Date à vérifier
 * @returns {boolean}
 */
export function isToday(date) {
  try {
    const d = typeof date === 'string' ? new Date(date) : date
    if (isNaN(d.getTime())) return false
    
    const today = new Date()
    return d.getDate() === today.getDate() &&
           d.getMonth() === today.getMonth() &&
           d.getFullYear() === today.getFullYear()
  } catch (error) {
    return false
  }
}

/**
 * Obtient le nom du mois en français
 * @param {number} monthIndex - Index du mois (0-11)
 * @returns {string}
 */
export function getMonthName(monthIndex) {
  const monthNames = [
    'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
    'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'
  ]
  return monthNames[monthIndex] || ''
}
