/**
 * RoboTarget Manager Component
 *
 * Alpine.js component for managing RoboTarget creation and submission
 */
import { popularTargets, getTargetsByDifficulty } from '../../data/popular-targets.js';

export default () => ({
  // State
  currentStep: 0, // Start at step 0 (welcome/catalog)
  isLoading: false,
  errorMessage: null,
  successMessage: null,

  // Mode selection
  creationMode: null, // 'assisted' or 'manual'
  catalogFilter: 'all', // 'all', 'beginner', 'intermediate', 'advanced'
  popularCatalog: popularTargets,

  // Target data
  target: {
    target_name: '',
    ra_j2000: '',
    dec_j2000: '',
    priority: 0,
    c_moon_down: false,
    c_hfd_mean_limit: null,
    c_alt_min: 30,
    shots: [],
  },

  // Current shot being edited
  currentShot: {
    filter_index: 0,
    filter_name: 'Luminance',
    exposure: 300,
    num: 10,
    gain: 100,
    offset: 50,
    bin: 1,
  },

  // Filter options
  filterOptions: [
    { index: 0, name: 'Luminance' },
    { index: 1, name: 'Red' },
    { index: 2, name: 'Green' },
    { index: 3, name: 'Blue' },
    { index: 4, name: 'Ha' },
    { index: 5, name: 'OIII' },
    { index: 6, name: 'SII' },
  ],

  // Pricing data
  pricing: {
    estimated_credits: 0,
    estimated_hours: 0,
    base_cost: 0,
    multipliers: {},
    breakdown: [],
  },

  // User subscription info (injected from backend)
  subscription: null,
  creditsBalance: 0,

  // Initialize
  init() {
    // Load subscription and credits from data attributes
    this.subscription = window.userSubscription || null;
    this.creditsBalance = window.userCredits || 0;

    // Load target templates from API
    this.loadTargetTemplates();

    // Auto-calculate pricing when relevant fields change
    this.$watch('target', () => {
      this.calculatePricing();
    }, { deep: true });
  },

  // Load target templates from API
  async loadTargetTemplates() {
    try {
      const apiUrl = `/api/target-templates`;

      console.log('📡 Loading templates from:', apiUrl);
      const response = await fetch(apiUrl);
      const result = await response.json();

      if (result.success && result.data) {
        console.log('✅ Templates loaded:', result.data.length, 'templates');
        console.log('📸 First template images:', result.data[0]?.preview_image, result.data[0]?.thumbnail_image);
        this.popularCatalog = result.data;
      } else {
        console.warn('⚠️ Failed to load target templates, using fallback');
        // Fallback to hardcoded templates if API fails
        this.popularCatalog = popularTargets;
      }
    } catch (error) {
      console.error('❌ Error loading target templates:', error);
      // Fallback to hardcoded templates
      this.popularCatalog = popularTargets;
    }
  },

  // Navigation
  nextStep() {
    if (this.validateCurrentStep()) {
      this.currentStep++;
      this.errorMessage = null;
    }
  },

  prevStep() {
    this.currentStep--;
    this.errorMessage = null;
  },

  goToStep(step) {
    this.currentStep = step;
    this.errorMessage = null;
  },

  // Validation
  validateCurrentStep() {
    this.errorMessage = null;

    switch (this.currentStep) {
      case 1: // Target info
        if (!this.target.target_name) {
          this.errorMessage = 'Le nom de la cible est requis';
          return false;
        }
        if (!this.validateRA(this.target.ra_j2000)) {
          this.errorMessage = 'Format RA invalide (format attendu: HH:MM:SS)';
          return false;
        }
        if (!this.validateDEC(this.target.dec_j2000)) {
          this.errorMessage = 'Format DEC invalide (format attendu: ±DD:MM:SS)';
          return false;
        }
        break;

      case 2: // Constraints
        if (this.target.c_alt_min < 0 || this.target.c_alt_min > 90) {
          this.errorMessage = 'Altitude minimale doit être entre 0° et 90°';
          return false;
        }
        if (this.target.c_hfd_mean_limit && (this.target.c_hfd_mean_limit < 1.5 || this.target.c_hfd_mean_limit > 4.0)) {
          this.errorMessage = 'HFD limite doit être entre 1.5 et 4.0';
          return false;
        }
        break;

      case 3: // Shots
        if (this.target.shots.length === 0) {
          this.errorMessage = 'Au moins un shot est requis';
          return false;
        }
        break;
    }

    return true;
  },

  validateRA(ra) {
    const raRegex = /^([0-1][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/;
    return raRegex.test(ra);
  },

  validateDEC(dec) {
    const decRegex = /^[+-]([0-8][0-9]|90):([0-5][0-9]):([0-5][0-9])$/;
    return decRegex.test(dec);
  },

  // Shot management
  addShot() {
    // Validate current shot
    if (this.currentShot.exposure < 0.1 || this.currentShot.exposure > 3600) {
      this.errorMessage = 'Exposition doit être entre 0.1 et 3600 secondes';
      return;
    }
    if (this.currentShot.num < 1 || this.currentShot.num > 1000) {
      this.errorMessage = 'Nombre de poses doit être entre 1 et 1000';
      return;
    }

    // Add shot to list
    this.target.shots.push({ ...this.currentShot });

    // Reset current shot
    this.currentShot = {
      filter_index: 0,
      filter_name: 'Luminance',
      exposure: 300,
      num: 10,
      gain: 100,
      offset: 50,
      bin: 1,
    };

    this.errorMessage = null;
  },

  removeShot(index) {
    this.target.shots.splice(index, 1);
  },

  editShot(index) {
    this.currentShot = { ...this.target.shots[index] };
    this.target.shots.splice(index, 1);
  },

  updateFilterName() {
    const filter = this.filterOptions.find(f => f.index === parseInt(this.currentShot.filter_index));
    if (filter) {
      this.currentShot.filter_name = filter.name;
    }
  },

  // Pricing calculation
  async calculatePricing() {
    if (!this.subscription) {
      return;
    }

    if (this.target.shots.length === 0) {
      this.pricing = {
        estimated_credits: 0,
        estimated_hours: 0,
        base_cost: 0,
        multipliers: {},
        breakdown: [],
      };
      return;
    }

    try {
      const response = await fetch('/api/pricing/estimate', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({
          subscription_plan: this.subscription.plan,
          target: {
            priority: this.target.priority,
            c_moon_down: this.target.c_moon_down,
            c_hfd_mean_limit: this.target.c_hfd_mean_limit,
            shots: this.target.shots,
          },
        }),
      });

      const data = await response.json();

      if (data.success) {
        this.pricing = data.estimation;
      } else {
        console.error('Pricing calculation failed:', data.message);
      }
    } catch (error) {
      console.error('Error calculating pricing:', error);
    }
  },

  // Submit target
  async submitTarget() {
    if (!this.validateCurrentStep()) {
      return;
    }

    // Check credits balance
    if (this.pricing.estimated_credits > this.creditsBalance) {
      this.errorMessage = `Crédits insuffisants. Requis: ${this.pricing.estimated_credits}, Disponible: ${this.creditsBalance}`;
      return;
    }

    this.isLoading = true;
    this.errorMessage = null;

    try {
      // Generate GUID for target
      const targetGuid = this.generateUUID();
      const setGuid = this.generateUUID();

      const payload = {
        guid_set: setGuid,
        guid_target: targetGuid,
        target_name: this.target.target_name,
        ra_j2000: this.target.ra_j2000,
        dec_j2000: this.target.dec_j2000,
        priority: this.target.priority,
        c_moon_down: this.target.c_moon_down,
        c_hfd_mean_limit: this.target.c_hfd_mean_limit,
        c_alt_min: this.target.c_alt_min,
        shots: this.target.shots,
      };

      const response = await fetch('/api/robotarget/targets', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify(payload),
      });

      const data = await response.json();

      if (data.success) {
        this.successMessage = 'Target créée avec succès !';

        // Redirect to targets list after 2 seconds
        setTimeout(() => {
          window.location.href = '/dashboard/robotarget';
        }, 2000);
      } else {
        this.errorMessage = data.message || 'Erreur lors de la création de la target';
      }
    } catch (error) {
      this.errorMessage = 'Erreur réseau lors de la création de la target';
      console.error('Submit error:', error);
    } finally {
      this.isLoading = false;
    }
  },

  // Utilities
  generateUUID() {
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
      const r = Math.random() * 16 | 0;
      const v = c === 'x' ? r : (r & 0x3 | 0x8);
      return v.toString(16);
    });
  },

  formatDuration(hours) {
    if (hours < 1) {
      return `${Math.round(hours * 60)} min`;
    }
    return `${hours.toFixed(1)} h`;
  },

  formatTime(seconds) {
    if (seconds < 60) {
      return `${seconds}s`;
    }
    const minutes = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${minutes}m ${secs}s`;
  },

  getTotalExposureTime() {
    return this.target.shots.reduce((total, shot) => {
      return total + (shot.exposure * shot.num);
    }, 0);
  },

  getTotalImages() {
    return this.target.shots.reduce((total, shot) => {
      return total + shot.num;
    }, 0);
  },

  // Priority helpers
  getPriorityLabel(priority) {
    const labels = {
      0: 'Très basse',
      1: 'Basse',
      2: 'Normale',
      3: 'Haute',
      4: 'Très haute',
    };
    return labels[priority] || 'Inconnue';
  },

  canUsePriority(priority) {
    if (!this.subscription) return false;

    const maxPriorities = {
      'stardust': 1,
      'nebula': 2,
      'quasar': 4,
    };

    return priority <= (maxPriorities[this.subscription.plan] || 0);
  },

  // Constraint helpers
  getConstraintIcon(constraint) {
    const icons = {
      'c_moon_down': '🌙',
      'c_hfd_mean_limit': '⭐',
      'c_alt_min': '📐',
    };
    return icons[constraint] || '•';
  },

  // ============================================
  // ASSISTED MODE & CATALOG METHODS
  // ============================================

  /**
   * Set creation mode (assisted or manual)
   */
  setMode(mode) {
    this.creationMode = mode;
  },

  /**
   * Start creation after mode selection
   */
  startCreation() {
    if (!this.creationMode) {
      this.errorMessage = 'Veuillez choisir un mode de création';
      return;
    }
    this.currentStep = 1;
  },

  /**
   * Get filtered catalog based on difficulty
   */
  getFilteredCatalog() {
    if (this.catalogFilter === 'all') {
      return this.popularCatalog;
    }
    return getTargetsByDifficulty(this.catalogFilter);
  },

  /**
   * Load a template target from catalog
   */
  loadTemplateTarget(template) {
    console.log('🎯 Loading template:', template.name);

    // Format RA coordinates (HH:MM:SS)
    const raHours = String(template.ra_hours).padStart(2, '0');
    const raMinutes = String(template.ra_minutes).padStart(2, '0');
    const raSeconds = String(Math.round(template.ra_seconds)).padStart(2, '0');
    const formattedRA = `${raHours}:${raMinutes}:${raSeconds}`;

    // Format DEC coordinates (+/-DD:MM:SS)
    const decSign = template.dec_degrees >= 0 ? '+' : '-';
    const decDegrees = String(Math.abs(template.dec_degrees)).padStart(2, '0');
    const decMinutes = String(template.dec_minutes).padStart(2, '0');
    const decSeconds = String(Math.round(template.dec_seconds)).padStart(2, '0');
    const formattedDEC = `${decSign}${decDegrees}:${decMinutes}:${decSeconds}`;

    // Format recommended shots
    const formattedShots = template.recommended_shots.map(shot => ({
      filter_index: this.getFilterIndexByName(shot.filter_name),
      filter_name: shot.filter_name,
      exposure: shot.exposure,
      num: shot.num,
      gain: 100,
      offset: 50,
      bin: shot.binning || 1,
    }));

    // Set constraints based on difficulty
    let priority = 0;
    let moonDown = false;
    let hfdLimit = null;

    if (template.difficulty === 'beginner') {
      priority = 0;
      moonDown = false;
      hfdLimit = null;
    } else if (template.difficulty === 'intermediate') {
      priority = 1;
      moonDown = false;
      hfdLimit = null;
    } else {
      priority = 2;
      moonDown = true;
      hfdLimit = 2.5;
    }

    // Show success message
    this.successMessage = `Template chargé: ${template.name} • ${template.tips}`;

    // STEP 1: Change to step 1 first
    this.currentStep = 1;

    // STEP 2: Wait for Alpine to render the new step, THEN assign values
    this.$nextTick(() => {
      console.log('🔄 Step 1 rendered, now assigning values...');

      // Assign properties one by one to preserve reactivity
      this.target.target_name = template.name;
      this.target.ra_j2000 = formattedRA;
      this.target.dec_j2000 = formattedDEC;
      this.target.priority = priority;
      this.target.c_moon_down = moonDown;
      this.target.c_hfd_mean_limit = hfdLimit;
      this.target.c_alt_min = 30;
      this.target.shots = formattedShots;

      console.log('✅ Template loaded successfully!');
      console.log('📝 Target values:', {
        target_name: this.target.target_name,
        ra_j2000: this.target.ra_j2000,
        dec_j2000: this.target.dec_j2000
      });

      // STEP 3: Force Alpine to update the DOM by triggering input events
      this.$nextTick(() => {
        const nameInput = document.querySelector('input[x-model="target.target_name"]');
        const raInput = document.querySelector('input[x-model="target.ra_j2000"]');
        const decInput = document.querySelector('input[x-model="target.dec_j2000"]');

        // Manually trigger input events to sync Alpine
        if (nameInput) {
          nameInput.value = this.target.target_name;
          nameInput.dispatchEvent(new Event('input', { bubbles: true }));
        }
        if (raInput) {
          raInput.value = this.target.ra_j2000;
          raInput.dispatchEvent(new Event('input', { bubbles: true }));
        }
        if (decInput) {
          decInput.value = this.target.dec_j2000;
          decInput.dispatchEvent(new Event('input', { bubbles: true }));
        }

        console.log('🔍 Values synced to DOM and Alpine notified');
      });
    });

    // Clear success message after 5 seconds
    setTimeout(() => {
      this.successMessage = null;
    }, 5000);
  },

  /**
   * Cancel template selection and return to catalog
   */
  cancelTemplateSelection() {
    console.log('🔙 Cancelling template selection, returning to catalog');

    // Reset target data
    this.target = {
      target_name: '',
      ra_j2000: '',
      dec_j2000: '',
      priority: 0,
      c_moon_down: false,
      c_hfd_mean_limit: null,
      c_alt_min: 30,
      shots: [],
    };

    // Clear messages
    this.successMessage = null;
    this.errorMessage = null;

    // Return to step 0 (catalog view)
    this.currentStep = 0;
  },

  /**
   * Get filter index by name
   */
  getFilterIndexByName(filterName) {
    const mapping = {
      'L': 0,
      'Luminance': 0,
      'R': 1,
      'Red': 1,
      'G': 2,
      'Green': 2,
      'B': 3,
      'Blue': 3,
      'Ha': 4,
      'H-alpha': 4,
      'OIII': 5,
      'O-III': 5,
      'SII': 6,
      'S-II': 6,
    };
    return mapping[filterName] || 0;
  },
});
