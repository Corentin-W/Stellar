#!/bin/bash

# Script de diagnostic de connexion Voyager
# Teste la connectivité au serveur Voyager sur différents ports

echo "╔═══════════════════════════════════════════════════════════════╗"
echo "║     DIAGNOSTIC DE CONNEXION VOYAGER                           ║"
echo "╚═══════════════════════════════════════════════════════════════╝"
echo ""

# Configuration
VOYAGER_HOST="185.228.120.120"
VOYAGER_PORT=5950
EMERGENCY_PORT=23002
TIMEOUT=3

echo "🔍 Serveur cible : $VOYAGER_HOST"
echo "🔍 Port API principal : $VOYAGER_PORT"
echo "🔍 Port d'urgence : $EMERGENCY_PORT"
echo ""

# Fonction pour tester un port
test_port() {
    local port=$1
    local description=$2

    echo -n "Test du port $port ($description)... "

    if nc -z -w $TIMEOUT $VOYAGER_HOST $port 2>/dev/null; then
        echo "✅ OUVERT"
        return 0
    else
        echo "❌ FERMÉ ou TIMEOUT"
        return 1
    fi
}

# Fonction pour tester la résolution DNS
test_dns() {
    echo -n "Résolution DNS de $VOYAGER_HOST... "
    if ping -c 1 -W 2 $VOYAGER_HOST >/dev/null 2>&1; then
        echo "✅ OK"
        return 0
    else
        echo "❌ ÉCHEC"
        return 1
    fi
}

# Test 1: Résolution DNS et ping
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "1️⃣  TEST DE RÉSEAU"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
test_dns
echo ""

# Test 2: Ports
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "2️⃣  TEST DES PORTS"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# Port principal API RoboTarget (5950)
if test_port $VOYAGER_PORT "API RoboTarget"; then
    PORT_5950_STATUS="✅ OUVERT"
    PORT_5950_OK=true
else
    PORT_5950_STATUS="❌ FERMÉ"
    PORT_5950_OK=false
fi

# Port d'urgence (23002)
if test_port $EMERGENCY_PORT "Signal d'urgence"; then
    PORT_23002_STATUS="✅ OUVERT"
    PORT_23002_OK=true
else
    PORT_23002_STATUS="❌ FERMÉ"
    PORT_23002_OK=false
fi

# Autres ports possibles
echo ""
echo "Test des ports alternatifs..."
test_port 5951 "Port alternatif 5951"
test_port 5900 "VNC standard (5900)"

echo ""

# Test 3: Tentative de connexion TCP complète
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "3️⃣  TEST DE CONNEXION TCP COMPLÈTE"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

if [ "$PORT_5950_OK" = true ]; then
    echo "Tentative de connexion TCP au port $VOYAGER_PORT..."
    echo "Attente de l'événement Version (timeout 5s)..."

    # Essayer de se connecter et lire la première ligne
    RESPONSE=$(timeout 5 nc $VOYAGER_HOST $VOYAGER_PORT 2>/dev/null | head -n 1)

    if [ -n "$RESPONSE" ]; then
        echo "✅ Données reçues du serveur !"
        echo "   Première ligne : ${RESPONSE:0:100}..."

        # Vérifier si c'est du JSON avec un événement Version
        if echo "$RESPONSE" | grep -q '"Event".*"Version"'; then
            echo "✅ Événement Version détecté - Voyager répond correctement !"
        else
            echo "⚠️  Données reçues mais format inattendu"
        fi
    else
        echo "❌ Aucune donnée reçue (timeout)"
        echo "   Le port est ouvert mais Voyager ne répond pas"
    fi
else
    echo "⏭️  Test ignoré (port $VOYAGER_PORT fermé)"
fi

echo ""

# Test 4: Vérification de la configuration .env
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "4️⃣  VÉRIFICATION DE LA CONFIGURATION .env"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

if [ -f ".env" ]; then
    echo "✅ Fichier .env trouvé"

    # Vérifier les variables importantes
    echo ""
    echo "Configuration actuelle :"
    grep "^VOYAGER_HOST=" .env 2>/dev/null && echo "   └─ Host configuré" || echo "   └─ ❌ VOYAGER_HOST manquant"
    grep "^VOYAGER_PORT=" .env 2>/dev/null && echo "   └─ Port configuré" || echo "   └─ ❌ VOYAGER_PORT manquant"
    grep "^VOYAGER_AUTH_ENABLED=" .env 2>/dev/null && echo "   └─ Auth activée" || echo "   └─ ⚠️  VOYAGER_AUTH_ENABLED non défini"
    grep "^VOYAGER_AUTH_BASE=" .env 2>/dev/null && echo "   └─ AuthBase configuré" || echo "   └─ ⚠️  VOYAGER_AUTH_BASE manquant"
    grep "^VOYAGER_MAC_KEY=" .env 2>/dev/null && echo "   └─ MAC Key configuré ($(grep "^VOYAGER_MAC_KEY=" .env | cut -d= -f2))" || echo "   └─ ⚠️  VOYAGER_MAC_KEY manquant"
else
    echo "❌ Fichier .env introuvable dans le répertoire courant"
fi

echo ""

# Résumé final
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "📊 RÉSUMÉ DU DIAGNOSTIC"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

if [ "$PORT_5950_OK" = true ]; then
    echo "✅ Le port principal $VOYAGER_PORT est OUVERT"
    echo ""
    echo "🎉 TOUT EST PRÊT !"
    echo ""
    echo "Vous pouvez maintenant démarrer le proxy avec :"
    echo "   npm run dev"
    echo ""
    echo "La connexion devrait s'établir automatiquement."
else
    echo "❌ PROBLÈME BLOQUANT DÉTECTÉ"
    echo ""
    echo "Le port principal $VOYAGER_PORT est FERMÉ sur $VOYAGER_HOST"
    echo ""
    echo "📋 ACTIONS REQUISES :"
    echo ""
    echo "1. Contactez Eric/Mike pour ouvrir le port TCP $VOYAGER_PORT"
    echo "   └─ Windows Firewall : Créer une règle entrante TCP $VOYAGER_PORT"
    echo ""
    echo "2. Vérifiez la configuration de Voyager :"
    echo "   └─ Setup → Voyager → Application Server"
    echo "   └─ Vérifier que le port est bien $VOYAGER_PORT"
    echo ""
    echo "3. Relancez ce diagnostic après les modifications :"
    echo "   └─ ./diagnostic-connexion.sh"
    echo ""
fi

if [ "$PORT_23002_OK" = true ]; then
    echo "ℹ️  Le port d'urgence $EMERGENCY_PORT est ouvert"
    echo "   (ce port ne peut PAS être utilisé pour l'API RoboTarget)"
fi

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "Diagnostic terminé - $(date '+%Y-%m-%d %H:%M:%S')"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
