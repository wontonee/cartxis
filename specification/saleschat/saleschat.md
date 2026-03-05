# Cartxis AI Sales Bot – Vector Storage Architecture Decision

## 1. Purpose of This Document

This document defines whether vector storage is required for the Cartxis AI Sales Bot and outlines the recommended architecture strategy for an open-source MySQL-based ecommerce system.

Cartxis is:
- Open-source
- MySQL-based
- Laravel-powered
- Designed for easy deployment

The goal is to keep the AI Sales Bot powerful but infrastructure-friendly.

---

# 2. What Is Vector Storage?

Vector storage (embedding database) is used for:

- Semantic search (meaning-based search)
- Similarity matching
- Contextual product discovery
- Mood/style-based search

Example:

User says:
"I want something stylish for a wedding under 2000"

Vector search:
- Converts sentence into embedding
- Matches products by meaning
- Returns relevant items even without exact keywords

Without vector search:
Only keyword-based search works.

---

# 3. Is Vector Storage Required for MVP?

No.

For 80% of ecommerce use cases, structured search is sufficient.

Most product queries are:
- Category-based
- Price-based
- Brand-based
- Variant-based
- Attribute-based

These can be handled using:

- MySQL filtering
- Full-text search
- AI-powered filter extraction

---

# 4. Recommended Architecture (Phase 1 – MVP)

## 4.1 No Vector Database

Use existing MySQL infrastructure.

### Flow:

User Input  
→ AI extracts structured filters  
→ Laravel builds dynamic query  
→ MySQL returns results  
→ AI formats response  

### Example:

User:
"Black sneakers size 9 under 3000"

AI Extracts:
- category = sneakers
- color = black
- size = 9
- price < 3000

Laravel Query:
- WHERE category_id = ?
- AND color = ?
- AND size = ?
- AND price <= ?

No embedding required.

---

# 5. Benefits of This Approach

- No additional infrastructure
- Fully open-source compatible
- Easy deployment
- No external paid dependency
- Works on shared hosting
- Easy adoption for developers

---

# 6. When Vector Storage Becomes Useful

Vector storage becomes valuable when:

- Store has 10,000+ products
- Fashion or lifestyle store
- Unstructured product descriptions
- Mood-based queries ("gift for girlfriend")
- Style similarity ("like Nike but cheaper")
- Advanced personalization

---

# 7. Future Architecture (Phase 2 – AI Commerce Pro)

Vector storage should be:

- Optional module
- Not mandatory for all users
- Available only in AI Pro plan

Possible options:
- PostgreSQL + pgvector
- Dedicated embedding service
- Hybrid MySQL + embedding ranking

### Hybrid Model:

1. MySQL filters by category/price
2. Embedding layer re-ranks top results
3. Return semantically optimized list

---

# 8. Strategic Recommendation for Cartxis

Phase 1:
- Use MySQL full-text search
- Use AI for filter extraction
- No vector database

Phase 2:
- Introduce optional semantic mode
- Make it part of AI Commerce Pro
- Keep fallback system for non-vector environments

---

# 9. Final Decision

Vector storage is NOT mandatory for AI Sales Bot MVP.

It is an enhancement layer for:
- Large stores
- Advanced discovery
- AI Commerce premium features

Cartxis AI Sales Bot should:

- Work perfectly without embeddings
- Be open-source friendly
- Be easy to deploy
- Scale modularly

---

# 10. Positioning

"Cartxis AI Sales Bot works instantly on your existing MySQL store — no complex infrastructure required."

Optional:
"Upgrade to AI Pro for advanced semantic discovery."