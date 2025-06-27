#!/bin/bash

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

API_URL="http://localhost:8000/api/v1"

echo -e "${BLUE}🔐 Testing Laundry API${NC}\n"

# 1. Login as Admin
echo -e "${GREEN}1. Login as Admin...${NC}"
LOGIN_RESPONSE=$(curl -s -X POST "$API_URL/auth/login" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@laundry.com",
    "password": "password123",
    "device_name": "testing"
  }')

TOKEN=$(echo $LOGIN_RESPONSE | grep -o '"token":"[^"]*' | sed 's/"token":"//')
echo "Token: $TOKEN"
echo ""

# 2. Get Profile
echo -e "${GREEN}2. Get Profile...${NC}"
curl -s -X GET "$API_URL/auth/profile" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" | jq '.'
echo ""

# 3. Get Prices
echo -e "${GREEN}3. Get Prices...${NC}"
curl -s -X GET "$API_URL/prices" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" | jq '.'
echo ""

# 4. Get Transactions
echo -e "${GREEN}4. Get Transactions...${NC}"
curl -s -X GET "$API_URL/transactions" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" | jq '.'
echo ""

echo -e "${BLUE}✅ Testing Complete!${NC}"