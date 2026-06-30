# Contract: Lottery Scoring Configuration

**Settings key:** `lottery.scoring.config`  
**Reader:** `LotteryScoringConfigReader`  
**Engine:** `LotteryScoringEngine`

## JSON shape

```json
{
  "version": "1.0.0",
  "base_score_coefficient": 1.0,
  "department_priority_coefficient": 0.05,
  "normalization_divisor": 100.0,
  "prng_scale": 1.0
}
```

## Formula

`weighted_score = normalized_weight + PRNG(random_seed, registration_id) * prng_scale`

Where:

`normalized_weight = (base_score * base_score_coefficient + department_priority * department_priority_coefficient) / normalization_divisor`

- `base_score` and `department_priority` come from `EmployeeLotteryScorePort`
- `PRNG` is deterministic SHA-256 derived in `LotteryScoringEngine::prngFactor`

## Lock behavior

- Config loaded once at lock; version stored on program and snapshot
- Missing/invalid config fails lock (no draw)
