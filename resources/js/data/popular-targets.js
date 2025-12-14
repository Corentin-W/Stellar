/**
 * Catalogue d'objets astronomiques populaires
 * Avec coordonnées préconfigurées et paramètres recommandés
 */

export const popularTargets = [
    {
        id: 'm42',
        name: 'M42 - Grande Nébuleuse d\'Orion',
        type: 'Nébuleuse',
        constellation: 'Orion',
        difficulty: 'beginner',
        description: 'La plus belle nébuleuse du ciel! Visible même en ville, parfaite pour débuter.',
        ra_hours: 5,
        ra_minutes: 35,
        ra_seconds: 17.3,
        dec_degrees: -5,
        dec_minutes: 23,
        dec_seconds: 28,
        best_months: ['Nov', 'Déc', 'Jan', 'Fév', 'Mar'],
        recommended_shots: [
            { filter_name: 'L', num: 20, exposure: 120, binning: 1 },
            { filter_name: 'R', num: 10, exposure: 120, binning: 1 },
            { filter_name: 'G', num: 10, exposure: 120, binning: 1 },
            { filter_name: 'B', num: 10, exposure: 120, binning: 1 },
        ],
        estimated_time: '2h20min',
        tips: 'Nébuleuse brillante, facile à imager. Essayez différents temps de pose pour éviter la saturation du noyau.'
    },
    {
        id: 'm31',
        name: 'M31 - Galaxie d\'Andromède',
        type: 'Galaxie',
        constellation: 'Andromède',
        difficulty: 'beginner',
        description: 'Notre voisine galactique! Immense et spectaculaire.',
        ra_hours: 0,
        ra_minutes: 42,
        ra_seconds: 44.3,
        dec_degrees: 41,
        dec_minutes: 16,
        dec_seconds: 9,
        best_months: ['Sep', 'Oct', 'Nov', 'Déc'],
        recommended_shots: [
            { filter_name: 'L', num: 30, exposure: 180, binning: 1 },
            { filter_name: 'R', num: 15, exposure: 180, binning: 1 },
            { filter_name: 'G', num: 15, exposure: 180, binning: 1 },
            { filter_name: 'B', num: 15, exposure: 180, binning: 1 },
        ],
        estimated_time: '3h45min',
        tips: 'Très grande! Utilisez un temps de pose moyen pour capturer les détails du noyau et des bras spiraux.'
    },
    {
        id: 'm13',
        name: 'M13 - Grand Amas d\'Hercule',
        type: 'Amas Globulaire',
        constellation: 'Hercule',
        difficulty: 'beginner',
        description: 'L\'amas globulaire le plus spectaculaire de l\'hémisphère nord.',
        ra_hours: 16,
        ra_minutes: 41,
        ra_seconds: 41.2,
        dec_degrees: 36,
        dec_minutes: 27,
        dec_seconds: 37,
        best_months: ['Avr', 'Mai', 'Juin', 'Jui', 'Aoû'],
        recommended_shots: [
            { filter_name: 'L', num: 30, exposure: 120, binning: 1 },
            { filter_name: 'R', num: 10, exposure: 120, binning: 1 },
            { filter_name: 'G', num: 10, exposure: 120, binning: 1 },
            { filter_name: 'B', num: 10, exposure: 120, binning: 1 },
        ],
        estimated_time: '2h',
        tips: 'Objet compact et brillant. Excellent pour tester votre configuration.'
    },
    {
        id: 'm51',
        name: 'M51 - Galaxie du Tourbillon',
        type: 'Galaxie',
        constellation: 'Chiens de Chasse',
        difficulty: 'intermediate',
        description: 'Galaxie spirale avec compagnon, structure magnifique!',
        ra_hours: 13,
        ra_minutes: 29,
        ra_seconds: 52.7,
        dec_degrees: 47,
        dec_minutes: 11,
        dec_seconds: 43,
        best_months: ['Mar', 'Avr', 'Mai', 'Juin'],
        recommended_shots: [
            { filter_name: 'L', num: 40, exposure: 240, binning: 1 },
            { filter_name: 'R', num: 20, exposure: 240, binning: 1 },
            { filter_name: 'G', num: 20, exposure: 240, binning: 1 },
            { filter_name: 'B', num: 20, exposure: 240, binning: 1 },
        ],
        estimated_time: '6h40min',
        tips: 'Galaxie plus faible, privilégiez les nuits noires pour maximiser le contraste.'
    },
    {
        id: 'ngc7000',
        name: 'NGC 7000 - Nébuleuse North America',
        type: 'Nébuleuse',
        constellation: 'Cygne',
        difficulty: 'intermediate',
        description: 'Grande nébuleuse diffuse en forme de continent américain.',
        ra_hours: 20,
        ra_minutes: 59,
        ra_seconds: 17.1,
        dec_degrees: 44,
        dec_minutes: 31,
        dec_seconds: 44,
        best_months: ['Juin', 'Jui', 'Aoû', 'Sep'],
        recommended_shots: [
            { filter_name: 'Ha', num: 30, exposure: 300, binning: 1 },
            { filter_name: 'OIII', num: 20, exposure: 300, binning: 1 },
            { filter_name: 'R', num: 15, exposure: 180, binning: 1 },
            { filter_name: 'G', num: 15, exposure: 180, binning: 1 },
            { filter_name: 'B', num: 15, exposure: 180, binning: 1 },
        ],
        estimated_time: '7h15min',
        tips: 'Idéale avec filtres à bande étroite (Ha, OIII). Très étendue, parfaite pour champ large.'
    },
    {
        id: 'm57',
        name: 'M57 - Nébuleuse de l\'Anneau',
        type: 'Nébuleuse Planétaire',
        constellation: 'Lyre',
        difficulty: 'intermediate',
        description: 'Nébuleuse planétaire iconique en forme d\'anneau parfait.',
        ra_hours: 18,
        ra_minutes: 53,
        ra_seconds: 35.1,
        dec_degrees: 33,
        dec_minutes: 1,
        dec_seconds: 45,
        best_months: ['Mai', 'Juin', 'Jui', 'Aoû', 'Sep'],
        recommended_shots: [
            { filter_name: 'L', num: 30, exposure: 180, binning: 1 },
            { filter_name: 'Ha', num: 20, exposure: 300, binning: 1 },
            { filter_name: 'OIII', num: 20, exposure: 300, binning: 1 },
            { filter_name: 'R', num: 10, exposure: 180, binning: 1 },
            { filter_name: 'G', num: 10, exposure: 180, binning: 1 },
            { filter_name: 'B', num: 10, exposure: 180, binning: 1 },
        ],
        estimated_time: '5h30min',
        tips: 'Petite mais brillante. Les filtres Ha et OIII révèlent des détails spectaculaires.'
    },
    {
        id: 'ic1396',
        name: 'IC 1396 - Nébuleuse de la Trompe d\'Éléphant',
        type: 'Nébuleuse',
        constellation: 'Céphée',
        difficulty: 'advanced',
        description: 'Région HII complexe avec piliers de création spectaculaires.',
        ra_hours: 21,
        ra_minutes: 39,
        ra_seconds: 0,
        dec_degrees: 57,
        dec_minutes: 30,
        dec_seconds: 0,
        best_months: ['Jui', 'Aoû', 'Sep', 'Oct'],
        recommended_shots: [
            { filter_name: 'Ha', num: 50, exposure: 300, binning: 1 },
            { filter_name: 'OIII', num: 30, exposure: 300, binning: 1 },
            { filter_name: 'SII', num: 30, exposure: 300, binning: 1 },
        ],
        estimated_time: '9h10min',
        tips: 'Projet avancé nécessitant filtres à bande étroite. Nuit noire fortement recommandée.'
    },
    {
        id: 'm27',
        name: 'M27 - Nébuleuse de l\'Haltère',
        type: 'Nébuleuse Planétaire',
        constellation: 'Renard',
        difficulty: 'beginner',
        description: 'Nébuleuse planétaire brillante et facile à imager.',
        ra_hours: 19,
        ra_minutes: 59,
        ra_seconds: 36.3,
        dec_degrees: 22,
        dec_minutes: 43,
        dec_seconds: 16,
        best_months: ['Juin', 'Jui', 'Aoû', 'Sep'],
        recommended_shots: [
            { filter_name: 'L', num: 25, exposure: 180, binning: 1 },
            { filter_name: 'R', num: 12, exposure: 180, binning: 1 },
            { filter_name: 'G', num: 12, exposure: 180, binning: 1 },
            { filter_name: 'B', num: 12, exposure: 180, binning: 1 },
        ],
        estimated_time: '3h3min',
        tips: 'Cible idéale pour débuter. Brillante et réactive à tous les filtres.'
    }
];

/**
 * Get targets filtered by difficulty
 */
export function getTargetsByDifficulty(difficulty) {
    return popularTargets.filter(t => t.difficulty === difficulty);
}

/**
 * Get target by ID
 */
export function getTargetById(id) {
    return popularTargets.find(t => t.id === id);
}

/**
 * Get targets visible in current month
 */
export function getCurrentMonthTargets() {
    const months = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Jui', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];
    const currentMonth = months[new Date().getMonth()];

    return popularTargets.filter(t => t.best_months.includes(currentMonth));
}
