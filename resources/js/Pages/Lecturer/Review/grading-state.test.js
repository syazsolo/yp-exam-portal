import assert from "node:assert/strict";
import test from "node:test";

import {
    cloneGradeMap,
    isGradeComplete,
    isGradeDirty,
    isGradeInvalid,
} from "./grading-state.js";

test("draft grades do not mutate saved grades", () => {
    const answer = { id: "3", question: { weight: 6 } };
    const savedGrades = cloneGradeMap({
        3: { score: 4, reviewer_comment: "Good structure." },
    });
    const draftGrades = cloneGradeMap(savedGrades);

    draftGrades[3].score = 5;

    assert.equal(savedGrades[3].score, 4);
    assert.equal(isGradeDirty(answer, draftGrades, savedGrades), true);
});

test("invalid scores are blocked before autosave", () => {
    const answer = { id: "3", question: { weight: 6 } };

    assert.equal(isGradeInvalid(answer, { 3: { score: "" } }), true);
    assert.equal(isGradeInvalid(answer, { 3: { score: -1 } }), true);
    assert.equal(isGradeInvalid(answer, { 3: { score: 7 } }), true);
    assert.equal(isGradeInvalid(answer, { 3: { score: "abc" } }), true);
    assert.equal(isGradeInvalid(answer, { 3: { score: 6 } }), false);
});

test("a saved zero score counts as graded", () => {
    const answer = { id: "3", question: { weight: 6 } };

    assert.equal(isGradeComplete(answer, { 3: { score: 0 } }), true);
});

test("an untouched blank grade is incomplete but not dirty", () => {
    const answer = { id: "3", question: { weight: 6 } };
    const blankGrade = { 3: { score: "", reviewer_comment: "" } };

    assert.equal(isGradeComplete(answer, blankGrade), false);
    assert.equal(isGradeDirty(answer, blankGrade, blankGrade), false);
});
