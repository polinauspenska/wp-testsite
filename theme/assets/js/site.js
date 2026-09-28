/* Lemon Mint Films — front-end behaviour.
   Deliberately small and dependency-free: no jQuery, no animation library.
   The guideline's competitive argument is that this site should not carry a
   page-builder fingerprint, and that starts with what ships to the browser. */
(function () {
	"use strict";

	/* ── mobile menu ─────────────────────────────────────────── */
	var burger = document.getElementById("burger");
	var sheet = document.getElementById("msheet");
	if (burger && sheet) {
		burger.addEventListener("click", function () {
			var open = sheet.classList.toggle("open");
			burger.setAttribute("aria-expanded", open ? "true" : "false");
			document.body.classList.toggle("noscroll", open);
		});
	}

	/* ── scroll reveal ───────────────────────────────────────── */
	if (!window.matchMedia("(prefers-reduced-motion:reduce)").matches && "IntersectionObserver" in window) {
		var io = new IntersectionObserver(function (entries) {
			entries.forEach(function (e) {
				if (e.isIntersecting) {
					e.target.classList.add("seen");
					io.unobserve(e.target);
				}
			});
		}, { rootMargin: "0px 0px -6% 0px", threshold: 0 });

		document.querySelectorAll(".sec, .grid > *, .svctable > a, .numlist > div").forEach(function (el) {
			el.classList.add("rv");
			io.observe(el);
		});
	}

	/* ── showreel takeover ───────────────────────────────────
	   Real video when a URL is set in the Customizer; otherwise a halftone
	   stand-in so the interaction can still be demonstrated to the client. */
	var reel = document.getElementById("reel");
	var reelInner = document.getElementById("reelInner");
	var reelBar = document.getElementById("reelBar");
	var reelT = document.getElementById("reelT");
	var timer = null;
	var secs = 0;

	function pad(n) { return (n < 10 ? "0" : "") + n; }

	function openReel() {
		if (!reel) { return; }
		reel.classList.add("open");
		reel.setAttribute("aria-hidden", "false");
		document.body.classList.add("noscroll");

		var url = reel.getAttribute("data-src");
		if (url) {
			/* Built as a node rather than concatenated into innerHTML — a URL from
			   the Customiser must never become markup. */
			var v = document.createElement("video");
			v.controls = true;
			v.autoplay = true;
			v.playsInline = true;
			v.style.cssText = "width:100%;height:100%;object-fit:cover";
			v.src = url;
			reelInner.innerHTML = "";
			reelInner.appendChild(v);
			return;
		}

		reelInner.innerHTML = '<canvas width="1280" height="720"></canvas>';
		var cv = reelInner.querySelector("canvas");
		var kinds = ["wide", "portrait", "interior"];
		var i = 0;
		if (window.LMFStill) { window.LMFStill(cv, "reel-0", kinds[0]); }
		secs = 0;
		clearInterval(timer);
		timer = setInterval(function () {
			secs++;
			if (secs % 3 === 0 && window.LMFStill) {
				i = (i + 1) % kinds.length;
				window.LMFStill(cv, "reel-" + secs, kinds[i]);
			}
			if (reelBar) { reelBar.style.width = Math.min(100, (secs / 90) * 100) + "%"; }
			if (reelT) { reelT.textContent = "00:" + pad(secs % 60); }
			if (secs >= 90) { closeReel(); }
		}, 1000);
	}

	function closeReel() {
		clearInterval(timer);
		if (!reel) { return; }
		reel.classList.remove("open");
		reel.setAttribute("aria-hidden", "true");
		document.body.classList.remove("noscroll");
		reelInner.innerHTML = "";
	}

	document.addEventListener("click", function (e) {
		if (e.target.closest("[data-reel]")) {
			e.preventDefault();
			openReel();
		}
	});
	var reelX = document.getElementById("reelX");
	if (reelX) { reelX.addEventListener("click", closeReel); }
	document.addEventListener("keydown", function (e) {
		if (e.key === "Escape") { closeReel(); }
	});

	/* ── contact form: intent routing ────────────────────────
	   Three doors, each pre-selecting the form's intent so the enquiry
	   arrives with context rather than as a blank "Contact Us". */
	var doors = document.querySelectorAll("[data-intent]");
	if (doors.length) {
		doors.forEach(function (b) {
			b.addEventListener("click", function () {
				doors.forEach(function (x) { x.classList.remove("on"); });
				b.classList.add("on");
				var sel = document.getElementById("lmf-intent");
				if (sel) { sel.value = b.getAttribute("data-intent"); }
			});
		});
	}

	/* Preselect from ?i= and ?svc= so a CTA carries its context across pages. */
	var params = new URLSearchParams(window.location.search);
	["i", "svc"].forEach(function (key) {
		var val = params.get(key);
		if (!val) { return; }
		var el = document.getElementById(key === "i" ? "lmf-intent" : "lmf-service");
		if (el) { el.value = val; }
		if (key === "i") {
			/* Escape before it reaches a selector: ?i=") would otherwise throw
			   a SyntaxError and kill the rest of this script. */
			var safe = window.CSS && CSS.escape ? CSS.escape(val) : val.replace(/[^a-z0-9_-]/gi, "");
			var match = document.querySelector('[data-intent="' + safe + '"]');
			if (match) {
				doors.forEach(function (x) { x.classList.remove("on"); });
				match.classList.add("on");
			}
		}
	});
})();
