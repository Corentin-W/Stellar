#!/bin/bash

HOST="185.228.120.120"

echo "╔════════════════════════════════════════════════════════╗"
echo "║        🔍 SCAN DE PORTS VOYAGER                        ║"
echo "╚════════════════════════════════════════════════════════╝"
echo ""
echo "🎯 Cible: $HOST"
echo ""

# Ports à tester (ports communs de Voyager)
PORTS=(
  "5950:Port par défaut Voyager"
  "5951:Port alternatif Voyager"
  "23002:Port configuré actuellement"
  "5900:VNC (parfois utilisé)"
  "8080:HTTP alternatif"
  "3000:Port de développement"
)

echo "📋 Test de connexion sur les ports courants..."
echo "─────────────────────────────────────────────────────────"

for port_info in "${PORTS[@]}"; do
  IFS=':' read -r port description <<< "$port_info"
  printf "  Port %-5s (%s): " "$port" "$description"

  # Test de connexion avec timeout de 2 secondes
  if nc -z -v -w 2 "$HOST" "$port" 2>&1 | grep -q "succeeded"; then
    echo "✅ OUVERT"

    # Si le port est ouvert, tester la réception de données
    echo "    └─> Test de réception de données..."

    # Créer un fichier temporaire pour stocker la réponse
    TEMP_FILE=$(mktemp)

    # Se connecter et attendre 3 secondes pour voir si on reçoit des données
    (sleep 3; echo "") | nc "$HOST" "$port" > "$TEMP_FILE" 2>&1 &
    NC_PID=$!
    sleep 3.5
    kill $NC_PID 2>/dev/null

    # Vérifier si des données ont été reçues
    if [ -s "$TEMP_FILE" ]; then
      DATA_SIZE=$(wc -c < "$TEMP_FILE")
      echo "    └─> ✅ DONNÉES REÇUES ($DATA_SIZE octets)"
      echo "    └─> 📦 Aperçu:"
      head -c 200 "$TEMP_FILE" | sed 's/^/        /'
      echo ""
      echo ""
      echo "    🎯 CE PORT SEMBLE ÊTRE LE BON !"
    else
      echo "    └─> ❌ Aucune donnée reçue"
    fi

    rm -f "$TEMP_FILE"
  else
    echo "❌ FERMÉ"
  fi
done

echo ""
echo "─────────────────────────────────────────────────────────"
echo "📊 Résumé: Testez les ports OUVERTS qui REÇOIVENT des données"
echo ""
