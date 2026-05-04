export function cloneGradeMap(gradeMap) {
    return Object.fromEntries(
        Object.entries(gradeMap ?? {}).map(([answerId, grade]) => [
            answerId,
            { ...grade },
        ]),
    );
}

export function normalizedGrade(answer, source) {
    const grade = source[answer.id] ?? {};

    return {
        score: normalizeScore(grade.score),
        reviewer_comment: grade.reviewer_comment ?? "",
    };
}

export function isGradeInvalid(answer, source) {
    const rawScore = source[answer.id]?.score;
    const score = normalizeScore(rawScore);

    return (
        rawScore === "" ||
        rawScore === null ||
        Number.isNaN(score) ||
        score < 0 ||
        score > Number(answer.question.weight)
    );
}

export function isGradeDirty(answer, grades, savedGrades) {
    const draft = normalizedGrade(answer, grades);
    const saved = normalizedGrade(answer, savedGrades);

    return (
        !scoresMatch(draft.score, saved.score) ||
        draft.reviewer_comment !== saved.reviewer_comment
    );
}

export function isGradeComplete(answer, savedGrades) {
    const score = savedGrades[answer.id]?.score;

    return score !== undefined && score !== null && score !== "";
}

function normalizeScore(score) {
    if (score === "" || score === null || score === undefined) {
        return Number.NaN;
    }

    return Number(score);
}

function scoresMatch(left, right) {
    if (Number.isNaN(left) && Number.isNaN(right)) {
        return true;
    }

    return left === right;
}
