/**
 * Validators for RoboTarget API
 */

/**
 * Validate Set creation payload
 */
function validateSet(req, res, next) {
  const { guid_set, set_name } = req.body;

  if (!guid_set) {
    return res.status(400).json({
      success: false,
      message: 'guid_set est requis',
    });
  }

  // Validate GUID format (UUID)
  const uuidRegex = /^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i;
  if (!uuidRegex.test(guid_set)) {
    return res.status(400).json({
      success: false,
      message: 'guid_set doit être un UUID valide',
    });
  }

  next();
}

/**
 * Validate Target creation payload
 */
function validateTarget(req, res, next) {
  const {
    GuidTarget,
    RefGuidSet,
    TargetName,
    RAJ2000,
    DECJ2000,
    Priority,
  } = req.body;

  const errors = [];

  // Required fields
  if (!GuidTarget) errors.push('GuidTarget est requis');
  if (!RefGuidSet) errors.push('RefGuidSet est requis');
  if (!TargetName) errors.push('TargetName est requis');
  if (!RAJ2000) errors.push('RAJ2000 est requis');
  if (!DECJ2000) errors.push('DECJ2000 est requis');
  if (Priority === undefined) errors.push('Priority est requis');

  // Validate formats
  const uuidRegex = /^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i;
  if (GuidTarget && !uuidRegex.test(GuidTarget)) {
    errors.push('GuidTarget doit être un UUID valide');
  }
  if (RefGuidSet && !uuidRegex.test(RefGuidSet)) {
    errors.push('RefGuidSet doit être un UUID valide');
  }

  // Validate RA format (HH:MM:SS)
  const raRegex = /^([0-1][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/;
  if (RAJ2000 && !raRegex.test(RAJ2000)) {
    errors.push('RAJ2000 doit être au format HH:MM:SS');
  }

  // Validate DEC format (+DD:MM:SS or -DD:MM:SS)
  const decRegex = /^[+-]([0-8][0-9]|90):([0-5][0-9]):([0-5][0-9])$/;
  if (DECJ2000 && !decRegex.test(DECJ2000)) {
    errors.push('DECJ2000 doit être au format +DD:MM:SS ou -DD:MM:SS');
  }

  // Validate Priority range (0-4)
  if (Priority !== undefined && (Priority < 0 || Priority > 4)) {
    errors.push('Priority doit être entre 0 et 4');
  }

  if (errors.length > 0) {
    return res.status(400).json({
      success: false,
      message: 'Validation échouée',
      errors: errors,
    });
  }

  next();
}

/**
 * Validate Shot payload
 */
function validateShot(req, res, next) {
  const {
    RefGuidTarget,
    FilterIndex,
    Exposure,
    Num,
  } = req.body;

  const errors = [];

  // Required fields
  if (!RefGuidTarget) errors.push('RefGuidTarget est requis');
  if (FilterIndex === undefined) errors.push('FilterIndex est requis');
  if (!Exposure) errors.push('Exposure est requis');
  if (!Num) errors.push('Num est requis');

  // Validate UUID
  const uuidRegex = /^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i;
  if (RefGuidTarget && !uuidRegex.test(RefGuidTarget)) {
    errors.push('RefGuidTarget doit être un UUID valide');
  }

  // Validate ranges
  if (FilterIndex !== undefined && (FilterIndex < 0 || FilterIndex > 20)) {
    errors.push('FilterIndex doit être entre 0 et 20');
  }

  if (Exposure && (Exposure < 0.1 || Exposure > 3600)) {
    errors.push('Exposure doit être entre 0.1 et 3600 secondes');
  }

  if (Num && (Num < 1 || Num > 1000)) {
    errors.push('Num doit être entre 1 et 1000');
  }

  if (errors.length > 0) {
    return res.status(400).json({
      success: false,
      message: 'Validation échouée',
      errors: errors,
    });
  }

  next();
}

export {
  validateSet,
  validateTarget,
  validateShot,
};
