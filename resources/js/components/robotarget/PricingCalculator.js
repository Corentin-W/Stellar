/**
 * Pricing Calculator Component
 *
 * Real-time pricing calculator for RoboTarget sessions
 */
export default () => ({
  // State
  isCalculating: false,
  pricing: null,
  error: null,

  // Configuration
  selectedPlan: 'nebula',
  targetConfig: {
    priority: 0,
    c_moon_down: false,
    c_hfd_mean_limit: null,
    shots: [],
  },

  // Current shot being edited
  currentShot: {
    exposure: 300,
    num: 10,
    filter: 'Luminance',
  },

  // Available plans
  plans: {
    stardust: {
      name: 'Stardust',
      price: 29,
      credits: 20,
      maxPriority: 1,
      color: 'blue',
    },
    nebula: {
      name: 'Nebula',
      price: 59,
      credits: 60,
      maxPriority: 2,
      color: 'purple',
    },
    quasar: {
      name: 'Quasar',
      price: 119,
      credits: 150,
      maxPriority: 4,
      color: 'yellow',
    },
  },

  // Filter options
  filters: ['Luminance', 'Red', 'Green', 'Blue', 'Ha', 'OIII', 'SII'],

  // Initialize
  init() {
    // Auto-calculate when config changes
    this.$watch('targetConfig', () => {
      this.calculate();
    }, { deep: true });

    this.$watch('selectedPlan', () => {
      // Reset priority if exceeds new plan limit
      const maxPriority = this.plans[this.selectedPlan].maxPriority;
      if (this.targetConfig.priority > maxPriority) {
        this.targetConfig.priority = maxPriority;
      }
      this.calculate();
    });
  },

  // Add shot to configuration
  addShot() {
    if (this.currentShot.exposure <= 0 || this.currentShot.num <= 0) {
      return;
    }

    this.targetConfig.shots.push({ ...this.currentShot });

    // Reset current shot
    this.currentShot = {
      exposure: 300,
      num: 10,
      filter: 'Luminance',
    };
  },

  removeShot(index) {
    this.targetConfig.shots.splice(index, 1);
  },

  // Calculate pricing
  async calculate() {
    if (this.targetConfig.shots.length === 0) {
      this.pricing = null;
      return;
    }

    this.isCalculating = true;
    this.error = null;

    try {
      const response = await fetch('/api/pricing/estimate', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({
          subscription_plan: this.selectedPlan,
          target: this.targetConfig,
        }),
      });

      const data = await response.json();

      if (data.success) {
        this.pricing = data.estimation;
      } else {
        this.error = data.message || 'Erreur de calcul';
      }
    } catch (error) {
      this.error = 'Erreur réseau';
      console.error('Pricing calculation error:', error);
    } finally {
      this.isCalculating = false;
    }
  },

  // Get recommendation for monthly usage
  async getRecommendation() {
    if (this.targetConfig.shots.length === 0) {
      return;
    }

    this.isCalculating = true;
    this.error = null;

    try {
      // Simulate monthly usage (user can adjust)
      const monthlyTargets = Array(5).fill(this.targetConfig);

      const response = await fetch('/api/pricing/recommend', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({
          monthly_targets: monthlyTargets,
        }),
      });

      const data = await response.json();

      if (data.success) {
        this.selectedPlan = data.recommended_plan;
        alert(`Plan recommandé: ${this.plans[data.recommended_plan].name}`);
      }
    } catch (error) {
      this.error = 'Erreur de recommandation';
      console.error('Recommendation error:', error);
    } finally {
      this.isCalculating = false;
    }
  },

  // Helpers
  getTotalExposure() {
    return this.targetConfig.shots.reduce((total, shot) => {
      return total + (shot.exposure * shot.num);
    }, 0);
  },

  getTotalImages() {
    return this.targetConfig.shots.reduce((total, shot) => {
      return total + shot.num;
    }, 0);
  },

  formatTime(seconds) {
    if (seconds < 60) {
      return `${seconds}s`;
    }
    const minutes = Math.floor(seconds / 60);
    const hours = Math.floor(minutes / 60);
    const remainingMinutes = minutes % 60;

    if (hours > 0) {
      return `${hours}h ${remainingMinutes}m`;
    }
    return `${minutes}m`;
  },

  formatDuration(hours) {
    if (hours < 1) {
      return `${Math.round(hours * 60)} min`;
    }
    return `${hours.toFixed(1)} heures`;
  },

  getPriorityLabel(priority) {
    const labels = ['Très basse', 'Basse', 'Normale', 'Haute', 'Très haute'];
    return labels[priority] || 'Inconnue';
  },

  getPriorityMultiplier() {
    return this.pricing?.multipliers?.priority_multiplier || 1.0;
  },

  getMoonDownMultiplier() {
    return this.targetConfig.c_moon_down ? 2.0 : 1.0;
  },

  getHFDMultiplier() {
    return this.targetConfig.c_hfd_mean_limit ? 1.5 : 1.0;
  },

  getTotalMultiplier() {
    return this.getPriorityMultiplier() * this.getMoonDownMultiplier() * this.getHFDMultiplier();
  },

  canAfford() {
    if (!this.pricing) return true;
    const plan = this.plans[this.selectedPlan];
    return this.pricing.estimated_credits <= plan.credits;
  },

  getAffordabilityColor() {
    if (!this.pricing) return 'gray';
    if (this.canAfford()) return 'green';
    return 'red';
  },

  resetCalculator() {
    this.targetConfig = {
      priority: 0,
      c_moon_down: false,
      c_hfd_mean_limit: null,
      shots: [],
    };
    this.currentShot = {
      exposure: 300,
      num: 10,
      filter: 'Luminance',
    };
    this.pricing = null;
    this.error = null;
  },
});
