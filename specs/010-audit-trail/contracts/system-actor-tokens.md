# Contract: System Actor Tokens

**Version:** 1.0.0  
**Spec:** spec10 Audit Trail  
**Status:** Planning — design baseline

---

## Purpose

Stable identifiers for non-human actors (UD-10-04 / US4).

---

## Tokens

| Token | Description |
| ----- | ----------- |
| `system:lottery_draw` | Lottery draw job execution |
| `system:reserve_promotion` | Reserve winner promotion job |
| `system:scheduler` | Laravel scheduler commands |
| `system:archive_job` | Audit/notification archival jobs |
| `system:migration` | One-off data migrations |

---

## Usage

```json
{
  "actorType": "system",
  "actorId": "system:lottery_draw",
  "metadata": { "jobId": "ExecuteLotteryDrawJob", "lotteryProgramId": "..." }
}
```

Human actors MUST use `actorType: user` with Identity user UUID.
