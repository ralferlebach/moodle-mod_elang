mod_elang — load tests (E6)
===========================

Read-endpoint load tests for the batched content-assembly web service
`mod_elang_get_version_content` — the read whose scaling was flattened by the
N+1 work. Two interchangeable runners are provided: **k6** (a single static
binary, JavaScript scenarios) and **Apache JMeter** (JVM, XML test plan). Both
hit the same endpoint with the same parameters so results are comparable.

> **Run against a disposable dev/staging site only.** The seeder writes data and
> enables the REST web-service protocol; never point it at production.

Quick start
-----------

From the plugin root:

```
make load-seed OPLOG=500      # seed a 500-cue exercise + mint a REST token
make load-k6                  # k6 run    (auto-reads the seeded params)
make jmeter                   # JMeter run (auto-reads the seeded params)
```

`make load-seed` runs `seed_large.php`, which prints and stores
`BASE_URL/TOKEN/CMID/VERSIONID` in `tests/load/.load-env`; the `load-k6` and
`jmeter` targets read that file, so no manual `eval` is needed. To target a site
you seeded by hand, pass the values yourself:

```
make load-k6 BASE_URL=https://moodle.example/moodle TOKEN=... CMID=12 VERSIONID=34
```

k6
--

`elang-read-endpoints.k6.js` ramps virtual users (default 25) and fails the run
if the endpoint errors on more than 1% of requests. It also guards p95 latency,
but that is a regression signal rather than an absolute SLA: latency is dominated
by the response payload size, so it scales with the number of cues. The default
seed (`OPLOG`, 500 cues) stays well under the default p95 budget; for a stress
run of several thousand cues (`make load-seed OPLOG=5000`), raise the budget with
`-e P95=<ms>`. Tunables via `-e`: `VUS`, `RAMPUP`, `DURATION`, `P95` (ms). `make
k6-setup` downloads the k6 binary locally if it is not on `PATH`.

JMeter
------

`elang-read-endpoints.jmx` runs a thread group (default 25 threads) and asserts
each response is HTTP 200 with no web-service `exception`. Tunables via `-J`:
`threads`, `rampup`, `loops`. `make jmeter-setup` downloads Apache JMeter; a JRE
must be installed to run it. Results are written to `elang-load-results.jtl`.

Ignored artefacts
-----------------

The downloaded k6 binary, the unpacked JMeter distribution, `.load-env` and the
`*.jtl` result files are git-ignored; only the scripts and this README are
tracked.
