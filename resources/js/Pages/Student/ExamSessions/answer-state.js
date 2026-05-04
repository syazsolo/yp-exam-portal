export function cloneAnswerMap(answerMap) {
    return Object.fromEntries(
        Object.entries(answerMap ?? {}).map(([questionId, answer]) => [
            questionId,
            { ...answer },
        ]),
    );
}

export function normalizedAnswer(question, source) {
    const answer = source[question.id] ?? {};

    return question.type === "mcq"
        ? { selected_option_id: answer.selected_option_id ?? null }
        : { text_answer: answer.text_answer ?? "" };
}

export function hasSavedAnswer(question, savedAnswers) {
    const saved = savedAnswers[question.id];

    return question.type === "mcq"
        ? saved?.selected_option_id !== undefined &&
              saved?.selected_option_id !== null
        : (saved?.text_answer ?? "").trim() !== "";
}

export function hasDraftAnswer(question, answers) {
    const draft = answers[question.id];

    return question.type === "mcq"
        ? draft?.selected_option_id !== undefined &&
              draft?.selected_option_id !== null
        : (draft?.text_answer ?? "").trim() !== "";
}

export function isAnswerDirty(question, answers, savedAnswers) {
    const draft = normalizedAnswer(question, answers);
    const saved = normalizedAnswer(question, savedAnswers);

    return question.type === "mcq"
        ? draft.selected_option_id !== saved.selected_option_id
        : draft.text_answer !== saved.text_answer;
}
