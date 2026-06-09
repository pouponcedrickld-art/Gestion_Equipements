/**
 * Utilitaires pour la génération de grille calendaire
 */

/**
 * Structure d'une cellule de jour
 * @typedef {Object} DayCell
 * @property {Date} date - Date de la cellule
 * @property {number} day - Numéro du jour
 * @property {boolean} isCurrentMonth - Si le jour appartient au mois actuel
 * @property {boolean} isToday - Si c'est aujourd'hui
 * @property {Array} maintenances - Maintenances pour cette journée
 * @property {Array} visibleMaintenances - Maintenances visibles (max 3)
 * @property {boolean} hasMore - Si il y a plus de 3 maintenances
 */

/**
 * Structure de grille mensuelle
 * @typedef {Object} MonthGrid
 * @property {number} year - Année
 * @property {number} month - Mois (0-11)
 * @property {string} monthName - Nom du mois
 * @property {Array<Array<DayCell>>} weeks - Tableau de semaines
 */

/**
 * Génère la structure de grille pour un mois donné
 * @param {number} year - Année
 * @param {number} month - Mois (0-11)
 * @param {Array} maintenances - Liste des maintenances du mois
 * @returns {MonthGrid}
 */
export function generateMonthGrid(year, month, maintenances = []) {
  const firstDay = new Date(year, month, 1)
  const lastDay = new Date(year, month + 1, 0)
  const today = new Date()
  today.setHours(0, 0, 0, 0)

  // Déterminer le premier jour de la semaine (Lundi = 1, Dimanche = 0)
  // On ajuste pour que Lundi soit le premier jour
  let firstWeekday = firstDay.getDay()
  firstWeekday = firstWeekday === 0 ? 6 : firstWeekday - 1 // Convertir Dimanche(0) en 6, et décaler les autres

  // Déterminer le dernier jour de la semaine
  let lastWeekday = lastDay.getDay()
  lastWeekday = lastWeekday === 0 ? 6 : lastWeekday - 1

  // Créer un index des maintenances par date pour recherche rapide
  const maintenancesByDate = createMaintenanceIndex(maintenances)

  const weeks = []
  let currentWeek = []

  // Ajouter les jours du mois précédent pour compléter la première semaine
  for (let i = firstWeekday - 1; i >= 0; i--) {
    const date = new Date(year, month, -i)
    currentWeek.push(createDayCell(date, false, today, maintenancesByDate))
  }

  // Ajouter tous les jours du mois actuel
  for (let day = 1; day <= lastDay.getDate(); day++) {
    const date = new Date(year, month, day)
    currentWeek.push(createDayCell(date, true, today, maintenancesByDate))

    // Si on atteint la fin de la semaine (Dimanche), on ajoute la semaine et on en commence une nouvelle
    if (currentWeek.length === 7) {
      weeks.push(currentWeek)
      currentWeek = []
    }
  }

  // Ajouter les jours du mois suivant pour compléter la dernière semaine
  if (currentWeek.length > 0) {
    let nextMonthDay = 1
    while (currentWeek.length < 7) {
      const date = new Date(year, month + 1, nextMonthDay)
      currentWeek.push(createDayCell(date, false, today, maintenancesByDate))
      nextMonthDay++
    }
    weeks.push(currentWeek)
  }

  return {
    year,
    month,
    monthName: getMonthName(month),
    weeks
  }
}

/**
 * Crée un index des maintenances par date pour recherche O(1)
 * @param {Array} maintenances - Liste des maintenances
 * @returns {Object} Map date -> maintenances
 */
function createMaintenanceIndex(maintenances) {
  const index = {}

  maintenances.forEach(maintenance => {
    const date = new Date(maintenance.date_prevue)
    const dateKey = getDateKey(date)

    if (!index[dateKey]) {
      index[dateKey] = []
    }
    index[dateKey].push(maintenance)
  })

  return index
}

/**
 * Crée une cellule de jour avec ses maintenances
 * @param {Date} date - Date de la cellule
 * @param {boolean} isCurrentMonth - Si appartient au mois actuel
 * @param {Date} today - Date d'aujourd'hui
 * @param {Object} maintenancesByDate - Index des maintenances par date
 * @returns {DayCell}
 */
function createDayCell(date, isCurrentMonth, today, maintenancesByDate) {
  const dateKey = getDateKey(date)
  const maintenances = maintenancesByDate[dateKey] || []

  // Trier les maintenances par heure
  const sortedMaintenances = [...maintenances].sort((a, b) => {
    return new Date(a.date_prevue) - new Date(b.date_prevue)
  })

  // Limiter à 3 maintenances visibles
  const MAX_VISIBLE = 3
  const visibleMaintenances = sortedMaintenances.slice(0, MAX_VISIBLE)
  const hasMore = sortedMaintenances.length > MAX_VISIBLE

  return {
    date: new Date(date),
    day: date.getDate(),
    isCurrentMonth,
    isToday: isSameDay(date, today),
    maintenances: sortedMaintenances,
    visibleMaintenances,
    hasMore,
    moreCount: hasMore ? sortedMaintenances.length - MAX_VISIBLE : 0
  }
}

/**
 * Génère une clé unique pour une date (YYYY-MM-DD)
 * @param {Date} date - Date
 * @returns {string}
 */
function getDateKey(date) {
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

/**
 * Vérifie si deux dates sont le même jour
 * @param {Date} date1 - Première date
 * @param {Date} date2 - Deuxième date
 * @returns {boolean}
 */
function isSameDay(date1, date2) {
  return (
    date1.getDate() === date2.getDate() &&
    date1.getMonth() === date2.getMonth() &&
    date1.getFullYear() === date2.getFullYear()
  )
}

/**
 * Obtient le nom du mois en français
 * @param {number} monthIndex - Index du mois (0-11)
 * @returns {string}
 */
function getMonthName(monthIndex) {
  const monthNames = [
    'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
    'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'
  ]
  return monthNames[monthIndex]
}

/**
 * Obtient les jours de la semaine en français (Lundi en premier)
 * @returns {Array<string>}
 */
export function getDaysOfWeek() {
  return ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim']
}

/**
 * Navigue vers un mois relatif
 * @param {Date} currentDate - Date actuelle
 * @param {number} offset - Décalage en mois (-1 pour mois précédent, +1 pour suivant)
 * @returns {Date}
 */
export function navigateMonth(currentDate, offset) {
  const newDate = new Date(currentDate)
  newDate.setMonth(newDate.getMonth() + offset)
  return newDate
}

/**
 * Obtient le nombre de maintenances par statut pour un jour donné
 * @param {DayCell} dayCell - Cellule de jour
 * @returns {Object} { planifiee: number, en_cours: number, terminee: number }
 */
export function getMaintenanceCountByStatus(dayCell) {
  const counts = {
    planifiee: 0,
    en_cours: 0,
    terminee: 0
  }

  dayCell.maintenances.forEach(maintenance => {
    if (counts.hasOwnProperty(maintenance.statut)) {
      counts[maintenance.statut]++
    }
  })

  return counts
}

/**
 * Filtre les maintenances par type
 * @param {Array} maintenances - Liste des maintenances
 * @param {string} type - Type de maintenance ('préventif' ou 'correctif')
 * @returns {Array}
 */
export function filterMaintenancesByType(maintenances, type) {
  if (!type || type === 'all') return maintenances
  return maintenances.filter(m => m.type_maintenance === type)
}

/**
 * Filtre les maintenances par statut
 * @param {Array} maintenances - Liste des maintenances
 * @param {string} statut - Statut ('planifiee', 'en_cours', 'terminee')
 * @returns {Array}
 */
export function filterMaintenancesByStatus(maintenances, statut) {
  if (!statut || statut === 'all') return maintenances
  return maintenances.filter(m => m.statut === statut)
}
