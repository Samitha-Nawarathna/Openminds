import { ROOT } from "../../core/config.js";

document.addEventListener("DOMContentLoaded", async () => {
	const els = {
		title: document.getElementById("review-title"),
		description: document.getElementById("review-description"),
		average: document.getElementById("average-score"),
		total: document.getElementById("total-questions"),
		countLabel: document.getElementById("question-count-label"),
		list: document.getElementById("review-questions"),
		empty: document.getElementById("review-empty"),
		endBtn: document.getElementById("end-review-btn")
	};

	if (!els.list) {
		return;
	}

	els.endBtn?.addEventListener("click", () => {
		window.location.href = `${ROOT}/exercises`;
	});

	try {
		const payload = await resolvePayload();
		const viewModel = normalizePayload(payload);
		renderReview(viewModel, els);
	} catch (error) {
		console.error("Review page load failed:", error);
		renderErrorState(els, error?.message || "Unable to load review data.");
	}
});

async function resolvePayload() {
	const initial = window.EXERCISE_REVIEW_INITIAL_DATA || window.EXERCISE_REVIEW_DATA || null;
	const params = new URLSearchParams(window.location.search);
	const attemptId = Number(params.get("attempt_id") || params.get("attempt") || 0);
	const exerciseId = Number(params.get("id") || params.get("exercise_id") || 0);

	if (hasRenderableQuestions(initial) && hasHighlightData(initial)) {
		return initial;
	}

	if (attemptId > 0) {
		const historyUrl = `${ROOT}/exercises/api/history/${attemptId}`;
		const historyData = await fetchOptionalJson(historyUrl);
		if (hasRenderableQuestions(historyData)) {
			return historyData;
		}
	}

	if (exerciseId > 0) {
		const attemptHistory = await fetchOptionalJson(`${ROOT}/exercises/api/history`);
		const latestAttemptId = pickLatestAttemptIdForExercise(attemptHistory, exerciseId);
		if (latestAttemptId > 0) {
			const latestAttemptData = await fetchOptionalJson(`${ROOT}/exercises/api/history/${latestAttemptId}`);
			if (hasRenderableQuestions(latestAttemptData)) {
				return latestAttemptData;
			}
		}

		const fallbackAttemptId = pickLatestAttemptId(attemptHistory);
		if (fallbackAttemptId > 0) {
			const fallbackAttemptData = await fetchOptionalJson(`${ROOT}/exercises/api/history/${fallbackAttemptId}`);
			if (hasRenderableQuestions(fallbackAttemptData) && isAttemptCompatibleWithInitial(fallbackAttemptData, initial)) {
				return fallbackAttemptData;
			}
		}

		const reviewUrl = `${ROOT}/exercises/api/load_review_data/${exerciseId}`;
		const reviewData = await fetchOptionalJson(reviewUrl);
		if (hasRenderableQuestions(reviewData)) {
			return reviewData;
		}
	}

	return initial || {};
}

function pickLatestAttemptIdForExercise(historyPayload, exerciseId) {
	if (!historyPayload || typeof historyPayload !== "object" || exerciseId <= 0) {
		return 0;
	}

	const attempts = Array.isArray(historyPayload.attempts) ? historyPayload.attempts : [];
	if (attempts.length === 0) {
		return 0;
	}

	for (const attempt of attempts) {
		if (!attempt || typeof attempt !== "object") {
			continue;
		}

		const attemptExerciseId = Number(
			attempt.exe_id ??
			attempt.exercise_id ??
			attempt.exerciseId ??
			attempt.ex_id ??
			0
		);

		if (attemptExerciseId !== exerciseId) {
			continue;
		}

		const resolvedAttemptId = Number(attempt.id ?? attempt.attempt_id ?? attempt.attemptId ?? 0);
		if (resolvedAttemptId > 0) {
			return resolvedAttemptId;
		}
	}

	return 0;
}

function pickLatestAttemptId(historyPayload) {
	if (!historyPayload || typeof historyPayload !== "object") {
		return 0;
	}

	const attempts = Array.isArray(historyPayload.attempts) ? historyPayload.attempts : [];
	for (const attempt of attempts) {
		if (!attempt || typeof attempt !== "object") {
			continue;
		}

		const resolvedAttemptId = Number(attempt.id ?? attempt.attempt_id ?? attempt.attemptId ?? 0);
		if (resolvedAttemptId > 0) {
			return resolvedAttemptId;
		}
	}

	return 0;
}

function isAttemptCompatibleWithInitial(attemptPayload, initialPayload) {
	if (!attemptPayload || !Array.isArray(attemptPayload.details)) {
		return false;
	}

	if (!initialPayload || !Array.isArray(initialPayload.questions) || initialPayload.questions.length === 0) {
		return true;
	}

	const initialQuestions = initialPayload.questions;
	const attemptQuestions = attemptPayload.details;

	if (attemptQuestions.length !== initialQuestions.length) {
		return false;
	}

	let matchedCount = 0;
	for (let i = 0; i < initialQuestions.length; i += 1) {
		const initialText = canonicalize(initialQuestions[i]?.question_text ?? initialQuestions[i]?.prompt ?? initialQuestions[i]?.text ?? "");
		const attemptText = canonicalize(attemptQuestions[i]?.prompt ?? attemptQuestions[i]?.question_text ?? attemptQuestions[i]?.text ?? "");

		if (!initialText || !attemptText) {
			continue;
		}

		if (initialText === attemptText) {
			matchedCount += 1;
		}
	}

	return matchedCount >= Math.ceil(initialQuestions.length * 0.6);
}

function hasRenderableQuestions(payload) {
	if (!payload || typeof payload !== "object") {
		return false;
	}

	if (Array.isArray(payload.questions) && payload.questions.length > 0) {
		return true;
	}

	if (Array.isArray(payload.details) && payload.details.length > 0) {
		return true;
	}

	return false;
}

function hasHighlightData(payload) {
	if (!payload || typeof payload !== "object") {
		return false;
	}

	if (Array.isArray(payload.details) && payload.details.length > 0) {
		return true;
	}

	if (payload.user_answers && Object.keys(payload.user_answers).length > 0) {
		return true;
	}

	if (payload.correct_answers && Object.keys(payload.correct_answers).length > 0) {
		return true;
	}

	const questions = Array.isArray(payload.questions) ? payload.questions : [];
	return questions.some((question) => {
		const options = Array.isArray(question?.options) ? question.options : [];
		return options.some((option) => {
			if (!option || typeof option !== "object") {
				return false;
			}

			return (
				Object.prototype.hasOwnProperty.call(option, "is_correct") ||
				Object.prototype.hasOwnProperty.call(option, "isCorrect") ||
				Object.prototype.hasOwnProperty.call(option, "was_selected") ||
				Object.prototype.hasOwnProperty.call(option, "wasSelected") ||
				Object.prototype.hasOwnProperty.call(option, "is_selected") ||
				Object.prototype.hasOwnProperty.call(option, "selected")
			);
		});
	});
}

async function fetchJson(url) {
	const response = await fetch(url, {
		method: "GET",
		headers: {
			"Accept": "application/json"
		}
	});

	let data = {};
	try {
		data = await response.json();
	} catch (_error) {
		data = {};
	}

	if (!response.ok || data.success === false) {
		throw new Error(data.message || `Request failed (${response.status})`);
	}

	return data;
}

async function fetchOptionalJson(url) {
	try {
		return await fetchJson(url);
	} catch (_error) {
		return null;
	}
}

function normalizePayload(payload) {
	const isAttemptDetails = Array.isArray(payload?.details);

	if (isAttemptDetails) {
		const questions = payload.details.map((item, index) => ({
			id: Number(item.question_id || index + 1),
			text: String(item.prompt || ""),
			difficulty: Number(item.difficulty ?? item.max_weight ?? 0),
			explanation: String(item.explanation || ""),
			options: (Array.isArray(item.options) ? item.options : []).map((opt) => ({
				key: String(opt.option_id ?? opt.id ?? opt.text ?? ""),
				text: String(opt.text || ""),
				isCorrect: Boolean(opt.is_correct ?? opt.isCorrect),
				isUserAnswer: Boolean(opt.was_selected ?? opt.wasSelected ?? opt.is_selected ?? opt.selected)
			}))
		}));

		return {
			title: String(payload.exercise_title || "Exercise Review"),
			description: "Review of your submitted attempt",
			averageScore: toPercentage(payload.percentage_score),
			totalQuestions: Number(payload.total_questions || questions.length),
			questions
		};
	}

	const sourceExercise = payload.exercise || payload.exercise_details || {};
	const sourceQuestions = Array.isArray(payload.questions) ? payload.questions : [];
	const userAnswers = payload.user_answers || {};
	const correctAnswers = payload.correct_answers || {};

	const questions = sourceQuestions.map((question, index) => {
		const qId = question.id ?? question.question_id ?? index + 1;
		const optionSource = Array.isArray(question.options) ? question.options : [];
		const normalizedUser = normalizeAnswerSet(userAnswers[qId] ?? userAnswers[String(qId)] ?? null);
		const normalizedCorrect = normalizeAnswerSet(correctAnswers[qId] ?? correctAnswers[String(qId)] ?? null);

		const options = optionSource.map((option, optIndex) => {
			const optionText = typeof option === "string" ? option : String(option?.text || option?.answer_text || "");
			const fallbackKey = optionText || String(optIndex + 1);
			const optionKey = typeof option === "string"
				? option
				: String(option?.id ?? option?.option_id ?? fallbackKey);
			const canonicalKey = canonicalize(optionKey);
			const canonicalText = canonicalize(optionText);
			const canonicalIndex = canonicalize(String(optIndex + 1));

			const hasExplicitCorrect = typeof option === "object" && option !== null && (
				Object.prototype.hasOwnProperty.call(option, "is_correct") ||
				Object.prototype.hasOwnProperty.call(option, "isCorrect")
			);

			const explicitCorrect = hasExplicitCorrect
				? Boolean(option.is_correct ?? option.isCorrect)
				: null;

			const hasExplicitSelected = typeof option === "object" && option !== null && (
				Object.prototype.hasOwnProperty.call(option, "was_selected") ||
				Object.prototype.hasOwnProperty.call(option, "wasSelected") ||
				Object.prototype.hasOwnProperty.call(option, "is_selected") ||
				Object.prototype.hasOwnProperty.call(option, "selected")
			);

			const explicitSelected = hasExplicitSelected
				? Boolean(option.was_selected ?? option.wasSelected ?? option.is_selected ?? option.selected)
				: null;

			const isCorrect = explicitCorrect !== null
				? explicitCorrect
				: normalizedCorrect.has(canonicalKey) || normalizedCorrect.has(canonicalText) || normalizedCorrect.has(canonicalIndex);

			const isUserAnswer = explicitSelected !== null
				? explicitSelected
				: normalizedUser.has(canonicalKey) || normalizedUser.has(canonicalText) || normalizedUser.has(canonicalIndex);

			return {
				key: optionKey,
				text: optionText,
				isCorrect,
				isUserAnswer
			};
		});

		return {
			id: Number(qId),
			text: String(question.question_text || question.prompt || question.text || ""),
			difficulty: Number(question.difficulty ?? question.weight ?? question.max_weight ?? 0),
			explanation: String(question.explanation || ""),
			options
		};
	});

	return {
		title: String(sourceExercise.title || payload.title || "Exercise Review"),
		description: String(sourceExercise.description || sourceExercise.role || payload.description || "Review questions and answers"),
		averageScore: toPercentage(payload.average_score ?? payload.percentage_score ?? 0),
		totalQuestions: Number(payload.total_questions || questions.length),
		questions
	};
}

function normalizeAnswerSet(value) {
	const result = new Set();

	if (Array.isArray(value)) {
		value.forEach((item, index) => {
			if (item && typeof item === "object") {
				const rawValue = item.id ?? item.option_id ?? item.value ?? item.text ?? item.answer_text ?? item.answer ?? item.option;
				if (rawValue !== undefined && rawValue !== null && rawValue !== "") {
					result.add(canonicalize(rawValue));
				}
				return;
			}

			result.add(canonicalize(item ?? String(index + 1)));
		});
		return result;
	}

	if (value && typeof value === "object") {
		Object.entries(value).forEach(([key, raw]) => {
			if (raw === true || raw === 1 || raw === "1") {
				result.add(canonicalize(key));
				return;
			}

			if (raw !== null && raw !== undefined && raw !== "" && raw !== false && raw !== 0 && raw !== "0") {
				result.add(canonicalize(raw));
			}
		});
		return result;
	}

	if (value !== null && value !== undefined && value !== "") {
		result.add(canonicalize(value));
	}

	return result;
}

function canonicalize(value) {
	return String(value ?? "").trim().toLowerCase();
}

function toPercentage(rawValue) {
	const numeric = Number(rawValue || 0);
	if (!Number.isFinite(numeric)) {
		return 0;
	}

	if (numeric <= 1 && numeric >= 0) {
		return Math.round(numeric * 100);
	}

	return Math.round(numeric);
}

function renderReview(model, els) {
	els.title.textContent = model.title || "Exercise Review";
	els.description.textContent = model.description || "Review questions and answers";
	els.average.textContent = `${toPercentage(model.averageScore)}%`;
	els.total.textContent = String(model.totalQuestions || model.questions.length || 0);
	els.countLabel.textContent = `${model.questions.length} items total`;

	els.list.innerHTML = "";

	if (!Array.isArray(model.questions) || model.questions.length === 0) {
		els.empty.hidden = false;
		return;
	}

	els.empty.hidden = true;

	model.questions.forEach((question, index) => {
		const card = document.createElement("article");
		card.className = "question-card";

		const head = document.createElement("div");
		head.className = "question-card__head";

		const number = document.createElement("span");
		number.className = "question-number";
		number.textContent = String(index + 1);

		const titleWrap = document.createElement("div");
		const questionTitle = document.createElement("h3");
		questionTitle.textContent = question.text || `Question ${index + 1}`;
		titleWrap.appendChild(questionTitle);

		const difficulty = Number(question.difficulty || 0);
		if (difficulty > 0) {
			const difficultyEl = document.createElement("p");
			difficultyEl.className = "question-difficulty";
			difficultyEl.textContent = `Difficulty: ${difficulty}`;
			titleWrap.appendChild(difficultyEl);
		}

		head.appendChild(number);
		head.appendChild(titleWrap);
		card.appendChild(head);

		const optionGrid = document.createElement("div");
		optionGrid.className = "option-grid";

		(question.options || []).forEach((option) => {
			const optionCard = document.createElement("div");
			optionCard.className = "option-card";

			if (option.isCorrect) {
				optionCard.classList.add("is-correct");
			}

			if (option.isUserAnswer && !option.isCorrect) {
				optionCard.classList.add("is-user-wrong");
			}

			const text = document.createElement("span");
			text.className = "option-text";
			text.textContent = option.text || "Option";
			optionCard.appendChild(text);

			if (option.isCorrect) {
				const badge = document.createElement("span");
				badge.className = "option-badge badge-correct";
				badge.textContent = "CORRECT";
				optionCard.appendChild(badge);
			} else if (option.isUserAnswer) {
				const badge = document.createElement("span");
				badge.className = "option-badge badge-user-wrong";
				badge.textContent = "YOUR ANSWER";
				optionCard.appendChild(badge);
			}

			optionGrid.appendChild(optionCard);
		});

		card.appendChild(optionGrid);

		if (question.explanation && question.explanation.trim() !== "") {
			const explanationWrap = document.createElement("div");
			explanationWrap.className = "explanation-block";

			const explanationTitle = document.createElement("h4");
			explanationTitle.className = "explanation-title";
			explanationTitle.textContent = "Explanation";

			const explanationText = document.createElement("p");
			explanationText.className = "explanation-text";
			explanationText.textContent = question.explanation;

			explanationWrap.appendChild(explanationTitle);
			explanationWrap.appendChild(explanationText);
			card.appendChild(explanationWrap);
		}

		els.list.appendChild(card);
	});
}

function renderErrorState(els, message) {
	els.title.textContent = "Review unavailable";
	els.description.textContent = "We could not load this review right now.";
	els.average.textContent = "0%";
	els.total.textContent = "0";
	els.countLabel.textContent = "0 items total";
	els.list.innerHTML = "";
	els.empty.hidden = false;

	const emptyMessage = els.empty.querySelector(".empty-state-message");
	if (emptyMessage) {
		emptyMessage.textContent = message;
	}
}
